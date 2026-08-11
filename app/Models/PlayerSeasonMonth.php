<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerSeasonMonth extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'goals' => 'integer',
            'assists' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(PlayerSeason::class, 'player_season_id');
    }
}
