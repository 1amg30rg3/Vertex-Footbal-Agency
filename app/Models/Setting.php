<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Support\Locales;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasMedia;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }

    /**
     * @return array<string, mixed>
     */
    public static function cached(): array
    {
        return Cache::rememberForever('settings.all', fn () => static::query()
            ->get(['key', 'value'])
            ->pluck('value', 'key')
            ->all());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::cached()[$key] ?? $default;
    }

    public static function put(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }

    public static function publicPayload(): array
    {
        $all = static::cached();
        $locale = app()->getLocale();

        $pick = fn (string $key, mixed $default = null) => Locales::pick($all[$key] ?? null, $locale) ?? $default;

        return [
            'name' => $pick('site_name', config('app.name')),
            'tagline' => $pick('site_tagline'),
            'logo' => static::mediaUrl($all['logo_path'] ?? null),
            'logo_light' => static::mediaUrl($all['logo_light_path'] ?? null),
            'share_image' => static::mediaUrl($all['share_image_path'] ?? null),
            'email' => $all['contact_email'] ?? null,
            'phone' => $all['contact_phone'] ?? null,
            'address' => $pick('contact_address'),
            'socials' => $all['socials'] ?? [],
            'featured_news_limit' => (int) ($all['featured_news_limit'] ?? 3),
            'copyright' => $pick('copyright'),
        ];
    }

    public static function defaults(): array
    {
        return [
            'site_name' => ['value' => Locales::normalizeMap([
                'ka' => 'VERTEX Football Agency',
                'en' => 'VERTEX Football Agency',
            ]), 'group' => 'general'],
            'site_tagline' => ['value' => Locales::normalizeMap([
                'ka' => 'მეტი, ვიდრე წარმომადგენლობა.',
                'en' => 'More Than Representation.',
                'de' => 'Mehr als Beratung.',
            ]), 'group' => 'general'],
            'contact_email' => ['value' => 'info@vertexfootball.example', 'group' => 'contact'],
            'contact_phone' => ['value' => '+995 555 00 00 00', 'group' => 'contact'],
            'contact_address' => ['value' => Locales::normalizeMap([
                'ka' => 'თბილისი, საქართველო',
                'en' => 'Tbilisi, Georgia',
            ]), 'group' => 'contact'],
            'socials' => ['value' => [
                ['platform' => 'instagram', 'url' => 'https://instagram.com/'],
                ['platform' => 'facebook', 'url' => 'https://facebook.com/'],
                ['platform' => 'linkedin', 'url' => 'https://linkedin.com/'],
            ], 'group' => 'contact'],
            'featured_news_limit' => ['value' => 3, 'group' => 'general'],
            'copyright' => ['value' => Locales::normalizeMap([
                'ka' => 'VERTEX Football Agency',
                'en' => 'VERTEX Football Agency',
            ]), 'group' => 'general'],
        ];
    }
}
