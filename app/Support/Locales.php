<?php

namespace App\Support;

use Illuminate\Support\Collection;

class Locales
{
    /** @return array<string, array{code:string,label:string,native:string,name:string,flag:string,dir:string}> */
    public static function all(): array
    {
        return config('localization.locales', []);
    }

    /** @return list<string> */
    public static function codes(): array
    {
        return array_keys(static::all());
    }

    public static function default(): string
    {
        return config('localization.default', 'ka');
    }

    public static function supports(?string $code): bool
    {
        return $code !== null && array_key_exists($code, static::all());
    }

    public static function find(?string $code): ?array
    {
        return static::all()[$code] ?? null;
    }

    /** @return Collection<int, array<string, string>> */
    public static function forSwitcher(): Collection
    {
        return collect(static::all())->values();
    }

    public static function routePattern(): string
    {
        return implode('|', static::codes());
    }

    public static function blankMap(mixed $value = null): array
    {
        return array_fill_keys(static::codes(), $value);
    }

    public static function normalizeMap(mixed $value, bool $trim = true): array
    {
        $value = is_array($value) ? $value : [];
        $out = [];

        foreach (static::codes() as $code) {
            $item = $value[$code] ?? null;

            if (is_string($item) && $trim) {
                $item = trim($item);
            }

            $out[$code] = ($item === '' || $item === null) ? null : $item;
        }

        return $out;
    }

    public static function pick(mixed $map, ?string $locale = null): ?string
    {
        if ($map === null) {
            return null;
        }

        if (is_string($map)) {
            return $map;
        }

        if (! is_array($map)) {
            return null;
        }

        foreach ([$locale ?? app()->getLocale(), static::default(), 'en'] as $code) {
            if (! empty($map[$code])) {
                return $map[$code];
            }
        }

        foreach ($map as $item) {
            if (! empty($item)) {
                return $item;
            }
        }

        return null;
    }
}
