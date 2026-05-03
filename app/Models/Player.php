<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'pseudo',
        'game_session_id',
        'is_active',
        'last_seen_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    /* ──────────────────── Relations ──────────────────── */

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /* ──────────────────── Méthodes métier ──────────────────── */

    /**
     * Vérifie si ce joueur a déjà voté sur une scène donnée dans une session donnée.
     */
    public function hasVotedOnScene(int $sceneId, int $sessionId): bool
    {
        return $this->votes()
            ->where('scene_id', $sceneId)
            ->where('game_session_id', $sessionId)
            ->exists();
    }

    /**
     * Retourne le vote du joueur pour une scène dans une session.
     */
    public function getVoteForScene(int $sceneId, int $sessionId): ?Vote
    {
        return $this->votes()
            ->where('scene_id', $sceneId)
            ->where('game_session_id', $sessionId)
            ->first();
    }

    /**
     * Met à jour le timestamp de présence sans toucher à is_active.
     *
     * Important : si le MJ a désactivé un joueur via le dashboard, on ne
     * veut surtout pas que la simple navigation suivante du joueur le
     * réactive automatiquement. Pour reprendre une session, il doit passer
     * par le formulaire de reconnexion.
     */
    public function ping(): void
    {
        $this->update(['last_seen_at' => now()]);
    }
}
