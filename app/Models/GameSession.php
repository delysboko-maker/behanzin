<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GameSession extends Model
{
    protected $fillable = [
        'code',
        'master_name',
        'master_token',
        'max_players',
        'current_scene_id',
        'status',
        'ending_type',
        'ending_title',
        'ending_subtitle',
        'ending_text',
    ];

    /* ──────────────────── Relations ──────────────────── */

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function currentScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'current_scene_id');
    }

    /* ──────────────────── Méthodes métier ──────────────────── */

    /**
     * Génère un code de session unique (ex : "REQUIN7").
     */
    public static function generateCode(?string $custom = null): string
    {
        if ($custom) {
            $code = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $custom));
            if (strlen($code) >= 3 && !self::where('code', $code)->exists()) {
                return $code;
            }
        }

        do {
            $words = ['REQUIN','ABOMEY','DANHOME','AGOLI','AMAZONE','VODOUN','COTONOU','OUIDAH'];
            $code  = $words[array_rand($words)] . rand(1, 99);
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Génère un token MJ aléatoire (URL-safe).
     */
    public static function generateMasterToken(): string
    {
        return Str::random(40);
    }

    /**
     * Vérifie si tous les joueurs actifs ont voté sur la scène courante.
     */
    public function allPlayersVoted(): bool
    {
        if (!$this->current_scene_id) {
            return false;
        }

        $activePlayers = $this->players()->where('is_active', true)->count();

        if ($activePlayers === 0) {
            return false;
        }

        $votedCount = Vote::where('game_session_id', $this->id)
            ->where('scene_id', $this->current_scene_id)
            ->count();

        return $votedCount >= $activePlayers;
    }

    /**
     * Compte les votes enregistrés pour la scène courante.
     */
    public function votedCount(): int
    {
        if (!$this->current_scene_id) {
            return 0;
        }

        return Vote::where('game_session_id', $this->id)
            ->where('scene_id', $this->current_scene_id)
            ->count();
    }

    /**
     * Avance à la scène suivante selon le choix majoritaire.
     *
     * Sécurisé contre la race-condition :
     *  - transaction
     *  - lockForUpdate sur la session courante
     *  - relit l'état après le verrou (au cas où un autre process aurait
     *    déjà avancé pendant l'attente du lock)
     *
     * Retourne la nouvelle scène ou null si fin / pas d'avancement nécessaire.
     */
    public function advanceToNextScene(): ?Scene
    {
        return DB::transaction(function () {
            // Pose un verrou exclusif sur la ligne de la session
            $session = self::where('id', $this->id)->lockForUpdate()->first();

            if (!$session || !$session->current_scene_id) {
                return null;
            }

            // Si quelqu'un a déjà fait avancer pendant l'attente du verrou,
            // l'état actuel ne correspond plus à $this : on relit et on sort.
            if ($session->status === 'finished') {
                $this->setRawAttributes($session->getAttributes(), true);
                return null;
            }

            $sceneId = $session->current_scene_id;

            // Tous les joueurs actifs ont-ils voté ? (revérifie sous lock)
            $activePlayers = $session->players()->where('is_active', true)->count();
            $votedCount    = Vote::where('game_session_id', $session->id)
                ->where('scene_id', $sceneId)
                ->count();

            if ($activePlayers === 0 || $votedCount < $activePlayers) {
                $this->setRawAttributes($session->getAttributes(), true);
                return null;
            }

            // Choix gagnant : majorité des votes
            $winningVote = Vote::where('game_session_id', $session->id)
                ->where('scene_id', $sceneId)
                ->selectRaw('choice_id, COUNT(*) as vote_count')
                ->groupBy('choice_id')
                ->orderByDesc('vote_count')
                ->orderBy('choice_id')      // tie-breaker déterministe
                ->first();

            if (!$winningVote) {
                return null;
            }

            $choice = Choice::find($winningVote->choice_id);

            if (!$choice || !$choice->next_scene_id) {
                $session->update(['status' => 'finished']);
                $this->setRawAttributes($session->getAttributes(), true);
                return null;
            }

            $nextScene = Scene::find($choice->next_scene_id);

            if (!$nextScene) {
                $session->update(['status' => 'finished']);
                $this->setRawAttributes($session->getAttributes(), true);
                return null;
            }

            $session->current_scene_id = $nextScene->id;
            $session->status           = $nextScene->is_ending ? 'finished' : 'active';

            if ($nextScene->is_ending) {
                $session->ending_type     = $nextScene->ending_type ?: 'unknown';
                $session->ending_title    = $nextScene->title;
                $session->ending_subtitle = $nextScene->quote ?? '';
                $session->ending_text     = $nextScene->content;
            }

            $session->save();

            // Synchronise l'instance appelante avec l'état persisté
            $this->setRawAttributes($session->getAttributes(), true);

            return $nextScene;
        });
    }

    /**
     * Démarre la session sur la première scène.
     * Idempotent : si déjà démarrée, ne fait rien.
     */
    public function start(): void
    {
        DB::transaction(function () {
            $session = self::where('id', $this->id)->lockForUpdate()->first();

            if (!$session || $session->current_scene_id) {
                if ($session) {
                    $this->setRawAttributes($session->getAttributes(), true);
                }
                return;
            }

            $firstScene = Scene::orderBy('sort_order')->first();
            if (!$firstScene) {
                return;
            }

            $session->update([
                'current_scene_id' => $firstScene->id,
                'status'           => 'active',
            ]);

            $this->setRawAttributes($session->getAttributes(), true);
        });
    }

    /**
     * Force l'avancement (utilisé par le MJ depuis le dashboard).
     * Même logique que advanceToNextScene mais ne vérifie pas que tout
     * le monde a voté — seulement qu'au moins un vote existe.
     */
    public function forceAdvance(): ?Scene
    {
        return DB::transaction(function () {
            $session = self::where('id', $this->id)->lockForUpdate()->first();
            if (!$session || !$session->current_scene_id || $session->status === 'finished') {
                return null;
            }

            $sceneId = $session->current_scene_id;

            $winningVote = Vote::where('game_session_id', $session->id)
                ->where('scene_id', $sceneId)
                ->selectRaw('choice_id, COUNT(*) as vote_count')
                ->groupBy('choice_id')
                ->orderByDesc('vote_count')
                ->orderBy('choice_id')
                ->first();

            // Aucun vote → on prend le premier choix par défaut
            $choice = $winningVote
                ? Choice::find($winningVote->choice_id)
                : Choice::where('scene_id', $sceneId)->orderBy('sort_order')->first();

            if (!$choice || !$choice->next_scene_id) {
                $session->update(['status' => 'finished']);
                $this->setRawAttributes($session->getAttributes(), true);
                return null;
            }

            $nextScene = Scene::find($choice->next_scene_id);
            if (!$nextScene) {
                $session->update(['status' => 'finished']);
                $this->setRawAttributes($session->getAttributes(), true);
                return null;
            }

            $session->current_scene_id = $nextScene->id;
            $session->status           = $nextScene->is_ending ? 'finished' : 'active';

            if ($nextScene->is_ending) {
                $session->ending_type     = $nextScene->ending_type ?: 'unknown';
                $session->ending_title    = $nextScene->title;
                $session->ending_subtitle = $nextScene->quote ?? '';
                $session->ending_text     = $nextScene->content;
            }

            $session->save();
            $this->setRawAttributes($session->getAttributes(), true);

            return $nextScene;
        });
    }

    /**
     * Termine la session (utilisé par le MJ).
     */
    public function endNow(): void
    {
        DB::transaction(function () {
            $session = self::where('id', $this->id)->lockForUpdate()->first();
            if (!$session) {
                return;
            }
            $session->update(['status' => 'finished']);
            $this->setRawAttributes($session->getAttributes(), true);
        });
    }

    /* ──────────────────── Scopes ──────────────────── */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }
}
