<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\GameSession;
use App\Models\Player;
use App\Models\Scene;
use App\Models\Vote;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GameController extends Controller
{
    /* ──────────────────────────────────────────────
     |  AFFICHAGE DE LA SCÈNE COURANTE
     ─────────────────────────────────────────────── */

    public function scene(Request $request, string $code): View|RedirectResponse
    {
        $session = $this->getSessionOrFail($code);
        $player  = $this->getPlayer($request);

        if ($player instanceof RedirectResponse) {
            return $player;
        }

        // Vérifier que le joueur appartient bien à cette session
        if ($player->game_session_id !== $session->id) {
            return redirect()->route('auth.join')
                ->withErrors(['code' => 'Vous n\'êtes pas inscrit dans cette session.']);
        }

        // Joueur désactivé (kicked par le MJ)
        if (!$player->is_active) {
            $request->session()->forget(['player_id', 'player_pseudo', 'session_code']);
            return redirect()->route('auth.login')
                ->with('info', 'Vous avez été désactivé par le maître de jeu. Reconnectez-vous si autorisé.');
        }

        // Ping du joueur (timestamp seulement, ne réactive pas)
        $player->ping();

        // Si pas de scène courante, démarrer
        if (!$session->current_scene_id) {
            $session->start();
            $session->refresh();
        }

        // Session terminée → redirection vers résultats
        if ($session->status === 'finished') {
            return redirect()->route('game.results', $code);
        }

        $scene = $session->currentScene;

        if (!$scene) {
            return redirect()->route('game.results', $code);
        }

        // Pagination des scènes pour la barre de progression
        $allScenes   = Scene::ordered()->nonEndings()->get();
        $sceneIndex  = $allScenes->search(fn ($s) => $s->id === $scene->id);
        $sceneIndex  = ($sceneIndex === false ? 0 : $sceneIndex) + 1;
        $totalScenes = max($allScenes->count(), 1);
        $progressPct = $scene->is_ending
            ? 100
            : min(100, (int) round(($sceneIndex / $totalScenes) * 100));

        $hasVoted   = $player->hasVotedOnScene($scene->id, $session->id);
        $votedCount = $session->votedCount();

        return view('game.scene', [
            'session'         => $session->load('players'),
            'scene'           => $scene->load('choices'),
            'player'          => $player,
            'hasVoted'        => $hasVoted,
            'votedCount'      => $votedCount,
            'sceneIndex'      => $sceneIndex,
            'totalScenes'     => $totalScenes,
            'progressPercent' => $progressPct,
        ]);
    }

    /* ──────────────────────────────────────────────
     |  SOUMETTRE UN VOTE  (atomique)
     ─────────────────────────────────────────────── */

    public function vote(Request $request, string $code, int $sceneId): RedirectResponse
    {
        $session = $this->getSessionOrFail($code);
        $player  = $this->getPlayer($request);

        if ($player instanceof RedirectResponse) {
            return $player;
        }

        if ($player->game_session_id !== $session->id) {
            abort(403, 'Session invalide.');
        }

        if (!$player->is_active) {
            return redirect()->route('auth.login')
                ->with('info', 'Joueur désactivé : impossible de voter.');
        }

        $request->validate([
            'choice_id' => ['required', 'integer', 'exists:choices,id'],
        ], [
            'choice_id.required' => 'Veuillez sélectionner un choix avant de voter.',
        ]);

        $scene = Scene::find($sceneId);
        if (!$scene) {
            return redirect()->route('game.scene', $code)
                ->with('error', 'Scène introuvable.');
        }

        if ($session->current_scene_id !== $scene->id) {
            return redirect()->route('game.scene', $code)
                ->with('error', 'La scène a changé. Actualisez la page.');
        }

        // Le choix doit appartenir à la scène
        $choice = Choice::where('id', $request->choice_id)
            ->where('scene_id', $scene->id)
            ->first();

        if (!$choice) {
            return redirect()->route('game.scene', $code)
                ->withErrors(['choice_id' => 'Choix invalide pour cette scène.']);
        }

        // Insertion + éventuel avancement, le tout sous transaction.
        // L'index unique (player_id, game_session_id, scene_id) garantit
        // qu'un double-clic ne crée pas deux votes même en cas de race.
        try {
            DB::transaction(function () use ($player, $session, $scene, $choice) {
                Vote::create([
                    'player_id'       => $player->id,
                    'game_session_id' => $session->id,
                    'scene_id'        => $scene->id,
                    'choice_id'       => $choice->id,
                ]);
            });
        } catch (QueryException $e) {
            // Violation d'index unique → l'utilisateur a déjà voté
            if ($this->isUniqueViolation($e)) {
                return redirect()->route('game.scene', $code)
                    ->with('info', 'Vous avez déjà voté pour cette scène.');
            }
            throw $e;
        }

        $player->ping();

        // L'avancement est lui-même protégé par lockForUpdate.
        // Plusieurs votes simultanés ne déclencheront qu'une seule avancée.
        $session->refresh();
        if ($session->allPlayersVoted()) {
            $session->advanceToNextScene();
        }

        return redirect()->route('game.scene', $code);
    }

    /* ──────────────────────────────────────────────
     |  POLLING AJAX — VÉRIFICATION D'AVANCEMENT
     ─────────────────────────────────────────────── */

    public function check(Request $request, string $code, int $sceneId): JsonResponse
    {
        $session = GameSession::where('code', strtoupper($code))->first();

        if (!$session) {
            return response()->json(['error' => 'session_not_found'], 404);
        }

        $session->refresh();
        $currentSceneId = $session->current_scene_id;

        if ($session->status === 'finished') {
            return response()->json([
                'ended'       => true,
                'results_url' => route('game.results', $code),
            ]);
        }

        if ($currentSceneId !== $sceneId) {
            return response()->json([
                'advanced' => true,
                'next_url' => route('game.scene', $code),
            ]);
        }

        return response()->json([
            'advanced'    => false,
            'ended'       => false,
            'voted_count' => $session->votedCount(),
            'players'     => $session->players()->where('is_active', true)->count(),
        ]);
    }

    /* ──────────────────────────────────────────────
     |  PAGE DE RÉSULTATS FINAUX
     ─────────────────────────────────────────────── */

    public function results(Request $request, string $code): View|RedirectResponse
    {
        $session = GameSession::where('code', strtoupper($code))->first();

        if (!$session) {
            return redirect()->route('welcome')
                ->withErrors(['code' => 'Session introuvable.']);
        }

        if ($session->status !== 'finished') {
            return redirect()->route('game.scene', $code);
        }

        $decisions = [];
        $scenes    = Scene::ordered()->nonEndings()->get();

        foreach ($scenes as $scene) {
            $winningVote = Vote::where('game_session_id', $session->id)
                ->where('scene_id', $scene->id)
                ->selectRaw('choice_id, COUNT(*) as vote_count')
                ->groupBy('choice_id')
                ->orderByDesc('vote_count')
                ->orderBy('choice_id')
                ->first();

            if ($winningVote) {
                $choice = Choice::find($winningVote->choice_id);
                $decisions[] = [
                    'scene_title'    => $scene->title,
                    'winning_choice' => $choice?->text ?? '—',
                    'vote_count'     => $winningVote->vote_count,
                ];
            }
        }

        $totalVotes   = Vote::where('game_session_id', $session->id)->count();
        $totalPlayers = $session->players()->count();
        $totalScenes  = count($decisions);

        return view('game.results', [
            'session'      => $session,
            'decisions'    => $decisions,
            'totalVotes'   => $totalVotes,
            'totalPlayers' => $totalPlayers,
            'totalScenes'  => $totalScenes,
        ]);
    }

    /* ──────────────────────────────────────────────
     |  ADMIN — CRÉER UNE SESSION
     ─────────────────────────────────────────────── */

    public function createSession(): View
    {
        return view('admin.create');
    }

    public function storeSession(Request $request): RedirectResponse
    {
        $request->validate([
            'master_name' => ['required', 'string', 'min:2', 'max:60'],
            'max_players' => ['required', 'integer', 'min:2', 'max:50'],
            'custom_code' => ['nullable', 'string', 'min:3', 'max:8',
                              'regex:/^[A-Z0-9]+$/i'],
        ], [
            'master_name.required' => 'Le nom du maître de jeu est obligatoire.',
            'custom_code.regex'    => 'Le code ne peut contenir que lettres et chiffres.',
        ]);

        $code  = GameSession::generateCode($request->custom_code);
        $token = GameSession::generateMasterToken();

        $session = GameSession::create([
            'code'         => $code,
            'master_name'  => trim($request->master_name),
            'master_token' => $token,
            'max_players'  => (int) $request->max_players,
            'status'       => 'waiting',
        ]);

        return redirect()
            ->route('admin.create')
            ->with('generated_code',  $code)
            ->with('master_name',     $session->master_name)
            ->with('master_token',    $token)
            ->with('master_url',      route('admin.dashboard', [$code, $token]))
            ->with('max_players',     $session->max_players)
            ->with('success',         'Session créée ! Code : ' . $code);
    }

    /* ──────────────────────────────────────────────
     |  DASHBOARD MJ
     ─────────────────────────────────────────────── */

    public function dashboard(string $code, string $token): View|RedirectResponse
    {
        $session = $this->authorizedMasterSession($code, $token);
        if ($session instanceof RedirectResponse) {
            return $session;
        }

        $scene = $session->currentScene;
        $players = $session->players()->orderBy('created_at')->get();
        $votedCount = $session->votedCount();

        return view('admin.dashboard', [
            'session'    => $session,
            'scene'      => $scene,
            'players'    => $players,
            'votedCount' => $votedCount,
            'token'      => $token,
        ]);
    }

    public function dashboardAdvance(string $code, string $token): RedirectResponse
    {
        $session = $this->authorizedMasterSession($code, $token);
        if ($session instanceof RedirectResponse) {
            return $session;
        }

        $session->forceAdvance();

        return redirect()->route('admin.dashboard', [$code, $token])
            ->with('success', 'Scène suivante déclenchée par le maître de jeu.');
    }

    public function dashboardEnd(string $code, string $token): RedirectResponse
    {
        $session = $this->authorizedMasterSession($code, $token);
        if ($session instanceof RedirectResponse) {
            return $session;
        }

        $session->endNow();

        return redirect()->route('admin.dashboard', [$code, $token])
            ->with('info', 'Session marquée comme terminée.');
    }

    public function dashboardKick(Request $request, string $code, string $token): RedirectResponse
    {
        $session = $this->authorizedMasterSession($code, $token);
        if ($session instanceof RedirectResponse) {
            return $session;
        }

        $request->validate([
            'player_id' => ['required', 'integer'],
        ]);

        Player::where('id', $request->player_id)
            ->where('game_session_id', $session->id)
            ->update(['is_active' => false]);

        return redirect()->route('admin.dashboard', [$code, $token])
            ->with('info', 'Joueur désactivé.');
    }

    /* ──────────────────────────────────────────────
     |  HELPERS PRIVÉS
     ─────────────────────────────────────────────── */

    private function getSessionOrFail(string $code): GameSession
    {
        $session = GameSession::where('code', strtoupper($code))->first();
        if (!$session) {
            abort(404, 'Session introuvable.');
        }
        return $session;
    }

    /**
     * Retourne le joueur courant ou un RedirectResponse propre.
     * Plus de findOrFail qui balance un 500 si l'ID est obsolète.
     */
    private function getPlayer(Request $request): Player|RedirectResponse
    {
        $playerId = $request->session()->get('player_id');
        if (!$playerId) {
            return redirect()->route('auth.join')
                ->with('info', 'Vous devez rejoindre une session pour accéder au jeu.');
        }

        $player = Player::find($playerId);
        if (!$player) {
            $request->session()->forget(['player_id', 'player_pseudo', 'session_code']);
            return redirect()->route('auth.join')
                ->with('info', 'Votre profil de joueur n\'existe plus. Rejoignez à nouveau.');
        }

        return $player;
    }

    private function authorizedMasterSession(string $code, string $token): GameSession|RedirectResponse
    {
        $session = GameSession::where('code', strtoupper($code))
            ->where('master_token', $token)
            ->first();

        if (!$session) {
            return redirect()->route('admin.create')
                ->withErrors(['token' => 'Lien maître de jeu invalide.']);
        }

        return $session;
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        // SQLSTATE 23000 = integrity constraint violation (incl. unique)
        // MySQL : code 1062. SQLite : message "UNIQUE constraint failed".
        $sqlState = $e->getCode();
        if ($sqlState === '23000' || $sqlState === 23000) {
            return true;
        }
        return str_contains($e->getMessage(), 'UNIQUE')
            || str_contains($e->getMessage(), 'Duplicate entry');
    }
}
