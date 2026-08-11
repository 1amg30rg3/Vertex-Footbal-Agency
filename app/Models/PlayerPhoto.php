<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class PlayerPhoto extends Model
{
    use HasFactory;
    use HasMedia;
    use HasTranslations;

    protected $guarded = ['id'];

    /** @var list<string> */
    public array $translatable = ['caption'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
