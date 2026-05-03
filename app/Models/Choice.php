<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Choice extends Model
{
    protected $fillable = [
        'scene_id',
        'text',
        'next_scene_id',
        'sort_order',
    ];

    /* ──────────────────── Relations ──────────────────── */

    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    public function nextScene(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'next_scene_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /* ──────────────────── Méthodes métier ──────────────────── */

    /**
     * Nombre de votes reçus dans une session.
     */
    public function voteCountInSession(int $sessionId): int
    {
        return $this->votes()->where('game_session_id', $sessionId)->count();
    }

    /**
     * Pourcentage des votes dans une session.
     */
    public function votePercentInSession(int $sessionId, int $totalVotes): int
    {
        if ($totalVotes === 0) return 0;
        return (int) round(($this->voteCountInSession($sessionId) / $totalVotes) * 100);
    }
}