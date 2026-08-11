<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerSeason extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'matches_played' => 'integer',
            'goals' => 'integer',
            'assists' => 'integer',
            'minutes_played' => 'integer',
            'starting_pct' => 'integer',
            'substitute_pct' => 'integer',
            'not_in_squad_pct' => 'integer',
            'is_current' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function months(): HasMany
    {
        return $this->hasMany(PlayerSeasonMonth::class)->orderBy('sort_order');
    }

    public function playingTimeSplit(): array
    {
        $slices = [
            'starting' => $this->starting_pct,
            'substitute' => $this->substitute_pct,
            'not_in_squad' => $this->not_in_squad_pct,
        ];

        $total = array_sum($slices);

        if ($total === 0) {
            return $slices;
        }

        return array_map(fn (int $value) => round($value / $total * 100, 1), $slices);
    }
}
