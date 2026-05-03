<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scene extends Model
{
    protected $fillable = [
        'act',
        'act_title',
        'year',
        'title',
        'content',
        'quote',
        'quote_author',
        'image_path',
        'question',
        'is_ending',
        'ending_type',   // resistance | exile | sacrifice (null sinon)
        'sort_order',
    ];

    protected $casts = [
        'is_ending' => 'boolean',
        'act'       => 'integer',
    ];

    /* ──────────────────── Relations ──────────────────── */

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class)->orderBy('sort_order');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /* ──────────────────── Scopes ──────────────────── */

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeByAct($query, int $act)
    {
        return $query->where('act', $act);
    }

    public function scopeEndings($query)
    {
        return $query->where('is_ending', true);
    }

    public function scopeNonEndings($query)
    {
        return $query->where('is_ending', false);
    }
}
