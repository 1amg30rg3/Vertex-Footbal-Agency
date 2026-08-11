<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class PlayerAchievement extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = ['id'];

    /** @var list<string> */
    public array $translatable = ['text'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
