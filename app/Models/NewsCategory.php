<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class NewsCategory extends Model
{
    use HasFactory;
    use HasSlug;
    use HasTranslations;

    protected $guarded = ['id'];

    /** @var list<string> */
    public array $translatable = ['name'];

    protected array $slugSource = ['name'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }
}
