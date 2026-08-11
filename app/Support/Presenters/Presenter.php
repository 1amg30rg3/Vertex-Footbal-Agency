<?php

namespace App\Support\Presenters;

use App\Support\Locales;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

abstract class Presenter
{
    public function __construct(protected ?string $locale = null)
    {
        $this->locale ??= app()->getLocale();
    }

    public static function make(?string $locale = null): static
    {
        return new static($locale);
    }

    protected function t(Model $model, string $attribute): ?string
    {
        /** @var HasTranslations $model */
        return Locales::pick($model->getTranslations($attribute), $this->locale);
    }

    protected function tm(mixed $map): ?string
    {
        return Locales::pick($map, $this->locale);
    }

    protected function date(mixed $value, string $format = 'Y-m-d'): ?string
    {
        return $value?->format($format);
    }

    /** @param iterable<Model> $items */
    public function collection(iterable $items, string $method = 'card'): array
    {
        return collect($items)->map(fn (Model $item) => $this->{$method}($item))->values()->all();
    }
}
