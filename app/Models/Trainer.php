<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSlug;
use App\Support\Locales;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Trainer extends Model
{
    use HasFactory;
    use HasMedia;
    use HasSlug;
    use HasTranslations;
    use SoftDeletes;

    public const STATUSES = ['draft', 'published'];

    protected $guarded = ['id'];

    /** @var list<string> */
    public array $translatable = [
        'first_name',
        'last_name',
        'role',
        'bio',
        'seo_title',
        'seo_description',
    ];

    protected array $slugSource = ['first_name', 'last_name'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'sort_order' => 'integer',
        ];
    }

    public function workEntries(): HasMany
    {
        return $this->hasMany(TrainerWorkEntry::class)->orderBy('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.str_replace('%', '\%', $term).'%';

        return $query->where(fn (Builder $q) => $q
            ->where('first_name', 'like', $like)
            ->orWhere('last_name', 'like', $like)
            ->orWhere('slug', 'like', $like));
    }

    public function fullName(?string $locale = null): string
    {
        return trim(implode(' ', array_filter([
            Locales::pick($this->getTranslations('first_name'), $locale),
            Locales::pick($this->getTranslations('last_name'), $locale),
        ])));
    }
}
