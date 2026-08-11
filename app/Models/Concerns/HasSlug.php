<?php

namespace App\Models\Concerns;

use App\Support\Locales;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

/**
 * @mixin Model
 * @mixin HasTranslations
 *
 * @property string $slug
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function (self $model) {
            if (blank($model->slug)) {
                $model->slug = $model->generateSlug();
            }

            $model->slug = $model->uniqueSlug(Str::slug($model->slug));
        });
    }

    protected function slugSourceCandidates(): array
    {
        $source = property_exists($this, 'slugSource') ? $this->slugSource : ['name'];
        $parts = [];

        $order = array_unique(array_merge(['en', Locales::default()], Locales::codes()));

        foreach ($order as $locale) {
            $value = collect($source)
                ->map(fn (string $field) => data_get($this->getTranslations($field), $locale))
                ->filter()
                ->implode(' ');

            if (filled($value)) {
                $parts[] = $value;
            }
        }

        return $parts;
    }

    protected function generateSlug(): string
    {
        foreach ($this->slugSourceCandidates() as $candidate) {
            $slug = Str::slug($candidate);

            if (filled($slug)) {
                return $slug;
            }
        }

        return Str::lower(class_basename($this)).'-'.Str::random(6);
    }

    protected function uniqueSlug(string $slug): string
    {
        $slug = filled($slug) ? $slug : Str::lower(class_basename($this)).'-'.Str::random(6);
        $base = $slug;
        $suffix = 2;

        while ($this->slugExists($slug)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    protected function slugExists(string $slug): bool
    {
        $query = static::query()->where('slug', $slug);

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        if (in_array(SoftDeletes::class, class_uses_recursive($this), true)) {
            $query->withTrashed();
        }

        return $query->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
