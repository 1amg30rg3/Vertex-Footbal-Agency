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
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

class Player extends Model
{
    use HasFactory;
    use HasMedia;
    use HasSlug;
    use HasTranslations;
    use SoftDeletes;

    public const POSITIONS = ['goalkeeper', 'defender', 'midfielder', 'forward'];

    public const FEET = ['left', 'right', 'both'];

    public const STATUSES = ['draft', 'published'];

    protected $guarded = ['id'];

    /** @var list<string> */
    public array $translatable = [
        'first_name',
        'last_name',
        'specific_position',
        'playing_style',
        'goals_short_term',
        'goals_mid_term',
        'goals_long_term',
        'quote',
        'seo_title',
        'seo_description',
    ];

    protected array $slugSource = ['first_name', 'last_name'];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'contract_until' => 'date',
            'is_featured' => 'boolean',
            'height_cm' => 'integer',
            'weight_kg' => 'integer',
            'pitch_x' => 'float',
            'pitch_y' => 'float',
            'sort_order' => 'integer',
        ];
    }

    public function skills(): HasMany
    {
        return $this->hasMany(PlayerSkill::class)->orderBy('sort_order');
    }

    public function careerEntries(): HasMany
    {
        return $this->hasMany(PlayerCareerEntry::class)->orderBy('sort_order');
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(PlayerAchievement::class)->orderBy('sort_order');
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(PlayerSeason::class)->orderBy('sort_order');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PlayerPhoto::class)->orderBy('sort_order');
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

        return $query->where(function (Builder $q) use ($like) {
            $q->where('first_name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhere('current_club', 'like', $like)
                ->orWhere('nationality', 'like', $like)
                ->orWhere('slug', 'like', $like);
        });
    }

    public function fullName(?string $locale = null): string
    {
        return trim(implode(' ', array_filter([
            Locales::pick($this->getTranslations('first_name'), $locale),
            Locales::pick($this->getTranslations('last_name'), $locale),
        ])));
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function currentSeason(): ?PlayerSeason
    {
        return $this->seasons->firstWhere('is_current', true) ?? $this->seasons->first();
    }

    public function careerTotals(): array
    {
        return [
            'matches' => (int) $this->seasons->sum('matches_played'),
            'goals' => (int) $this->seasons->sum('goals'),
            'assists' => (int) $this->seasons->sum('assists'),
            'minutes' => (int) $this->seasons->sum('minutes_played'),
        ];
    }

    public function contractExpiresSoon(): bool
    {
        return $this->contract_until !== null
            && $this->contract_until->isBetween(Carbon::now(), Carbon::now()->addMonths(6));
    }
}
