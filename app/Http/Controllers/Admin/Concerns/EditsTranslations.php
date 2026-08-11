<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\Locales;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

trait EditsTranslations
{
    protected function map(Model $model, string $attribute): array
    {
        /** @var HasTranslations $model */
        $stored = array_filter(
            $model->getTranslations($attribute),
            fn ($key) => in_array($key, Locales::codes(), true),
            ARRAY_FILTER_USE_KEY
        );

        return array_merge(Locales::blankMap(''), $stored);
    }

    protected function mapRaw(mixed $value): array
    {
        $stored = is_array($value)
            ? array_filter($value, fn ($key) => in_array($key, Locales::codes(), true), ARRAY_FILTER_USE_KEY)
            : [];

        return array_merge(Locales::blankMap(''), $stored);
    }
}
