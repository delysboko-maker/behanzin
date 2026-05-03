<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    /* ──────── Formulaire Rejoindre ──────── */

    public function showJoin(): View
    {
        return view('auth.join');
    }

    public function join(Request $request): RedirectResponse
    {
        $request->validate([
            'pseudo' => ['required', 'string', 'min:2', 'max:32',
                         'regex:/^[\p{L}0-9 _\-\.]+$/u'],
            'code'   => ['required', 'string', 'min:3', 'max:8'],
        ], [
            'pseudo.required' => 'Le pseudo est obligatoire.',
            'pseudo.min'      => 'Le pseudo doit faire au moins 2 caractères.',
            'pseudo.regex'    => 'Le pseudo ne peut contenir que lettres, chiffres et -_.',
            'code.required'   => 'Le code de session est obligatoire.',
        ]);

        $code    = strtoupper(trim($request->code));
        $pseudo  = trim($request->pseudo);

        // Vérifier que la session existe et est ouverte
        $session = GameSession::where('code', $code)
            ->whereIn('status', ['waiting', 'active'])
            ->first();

        if (!$session) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'Session introuvable ou terminée. Vérifiez le code.']);
        }

        // Vérifier la capacité
        if ($session->players()->where('is_active', true)->count() >= $session->max_players) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'La session est complète (' . $session->max_players . ' joueurs max).']);
        }

        // Vérifier pseudo déjà pris dans cette session
        $existing = Player::where('game_session_id', $session->id)
            ->whereRaw('LOWER(pseudo) = ?', [strtolower($pseudo)])
            ->first();

        if ($existing) {
            return back()
                ->withInput()
                ->withErrors(['pseudo' => 'Ce pseudo est déjà utilisé dans cette session.']);
        }

        // Créer le joueur
        $player = Player::create([
            'pseudo'          => $pseudo,
            'game_session_id' => $session->id,
            'is_active'       => true,
            'last_seen_at'    => now(),
        ]);

        // Démarrer la session si elle est en attente
        if ($session->status === 'waiting') {
            $session->start();
        }

        // Stocker en session HTTP
        $request->session()->put('player_id',     $player->id);
        $request->session()->put('player_pseudo',  $player->pseudo);
        $request->session()->put('session_code',   $session->code);

        return redirect()
            ->route('game.scene', $session->code)
            ->with('success', 'Bienvenue, ' . $pseudo . ' ! Le royaume attend votre sagesse.');
    }

    /* ──────── Formulaire Reconnexion ──────── */

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'pseudo' => ['required', 'string', 'min:2', 'max:32'],
            'code'   => ['required', 'string', 'min:3', 'max:8'],
        ]);

        $code   = strtoupper(trim($request->code));
        $pseudo = trim($request->pseudo);

        $session = GameSession::where('code', $code)
            ->whereIn('status', ['waiting', 'active'])
            ->first();

        if (!$session) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'Session introuvable ou terminée.']);
        }

        $player = Player::where('game_session_id', $session->id)
            ->whereRaw('LOWER(pseudo) = ?', [strtolower($pseudo)])
            ->first();

        if (!$player) {
            return back()
                ->withInput()
                ->withErrors(['pseudo' => 'Aucun joueur avec ce pseudo dans cette session. Utilisez "Rejoindre" pour créer votre profil.']);
        }

        // Réactiver le joueur
        $player->update(['is_active' => true, 'last_seen_at' => now()]);

        $request->session()->put('player_id',     $player->id);
        $request->session()->put('player_pseudo',  $player->pseudo);
        $request->session()->put('session_code',   $session->code);

        return redirect()
            ->route('game.scene', $session->code)
            ->with('success', 'Reconnexion réussie. Bienvenue, ' . $pseudo . ' !');
    }

    /* ──────── Déconnexion ──────── */

    public function logout(Request $request): RedirectResponse
    {
        // Marquer le joueur inactif
        $playerId = $request->session()->get('player_id');
        if ($playerId) {
            Player::where('id', $playerId)->update(['is_active' => false]);
        }

        $request->session()->forget(['player_id', 'player_pseudo', 'session_code']);

        return redirect()
            ->route('welcome')
            ->with('info', 'Vous avez quitté le conseil. À bientôt.');
    }
}