<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSlug;
use App\Support\Locales;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class News extends Model
{
    use HasFactory;
    use HasMedia;
    use HasSlug;
    use HasTranslations;
    use SoftDeletes;

    public const STATUSES = ['draft', 'published', 'scheduled'];

    protected $table = 'news';

    protected $guarded = ['id'];

    /** @var list<string> */
    public array $translatable = [
        'title',
        'excerpt',
        'body',
        'seo_title',
        'seo_description',
    ];

    protected array $slugSource = ['title'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'featured_order' => 'integer',
            'views' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'news_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where(function (Builder $sub) {
                $sub->where('status', 'published')
                    ->where(fn (Builder $w) => $w->whereNull('published_at')
                        ->orWhere('published_at', '<=', now()));
            })->orWhere(function (Builder $sub) {
                $sub->where('status', 'scheduled')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            });
        });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)
            ->orderBy('featured_order')
            ->orderByDesc('published_at');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.str_replace('%', '\%', $term).'%';

        return $query->where(fn (Builder $q) => $q
            ->where('title', 'like', $like)
            ->orWhere('excerpt', 'like', $like)
            ->orWhere('slug', 'like', $like));
    }

    public function isLive(): bool
    {
        return match ($this->status) {
            'published' => $this->published_at === null || $this->published_at->isPast(),
            'scheduled' => $this->published_at !== null && $this->published_at->isPast(),
            default => false,
        };
    }

    public function readingMinutes(?string $locale = null): int
    {
        $body = Locales::pick($this->getTranslations('body'), $locale) ?? '';
        $words = str_word_count(strip_tags($body));

        if ($words < 20) {
            $words = (int) ceil(mb_strlen(strip_tags($body)) / 6);
        }

        return max(1, (int) ceil($words / 200));
    }
}
