<?php

namespace App\Support;

use Illuminate\Support\Str;

/** Classifies a gallery row's stored path: image, uploaded video, or embed. */
class PlayerMedia
{
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

    public const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'm4v'];

    public const KIND_IMAGE = 'image';

    public const KIND_VIDEO = 'video';

    public const KIND_EMBED = 'embed';

    public static function kind(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (static::isExternal($path)) {
            return match (true) {
                static::embed($path) !== null => static::KIND_EMBED,
                static::hasExtension($path, static::VIDEO_EXTENSIONS) => static::KIND_VIDEO,
                default => null,
            };
        }

        return static::hasExtension($path, static::VIDEO_EXTENSIONS)
            ? static::KIND_VIDEO
            : static::KIND_IMAGE;
    }

    public static function isExternal(?string $value): bool
    {
        return is_string($value) && Str::startsWith($value, ['http://', 'https://', '//']);
    }

    /** @return array{provider: string, id: string}|null */
    public static function embed(?string $url): ?array
    {
        if (! static::isExternal($url)) {
            return null;
        }

        $host = Str::of((string) parse_url($url, PHP_URL_HOST))->lower()->after('www.')->toString();
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $found = match (true) {
            $host === 'youtu.be' => ['youtube', Str::before($path, '/')],

            in_array($host, ['youtube.com', 'youtube-nocookie.com', 'm.youtube.com'], true) => match (true) {
                filled($query['v'] ?? null) => ['youtube', (string) $query['v']],
                Str::startsWith($path, ['embed/', 'shorts/', 'v/']) => ['youtube', Str::afterLast($path, '/')],
                default => null,
            },

            $host === 'vimeo.com' => ['vimeo', Str::afterLast($path, '/')],
            $host === 'player.vimeo.com' => ['vimeo', Str::afterLast($path, '/')],

            default => null,
        };

        if ($found === null) {
            return null;
        }

        [$provider, $id] = $found;

        $valid = $provider === 'vimeo'
            ? (bool) preg_match('/^\d+$/', $id)
            : (bool) preg_match('/^[A-Za-z0-9_-]{6,20}$/', $id);

        return $valid ? ['provider' => $provider, 'id' => $id] : null;
    }

    public static function embedUrl(?string $url): ?string
    {
        $embed = static::embed($url);

        return match ($embed['provider'] ?? null) {
            'youtube' => 'https://www.youtube-nocookie.com/embed/'.$embed['id'],
            'vimeo' => 'https://player.vimeo.com/video/'.$embed['id'],
            default => null,
        };
    }

    /** Poster still for a link. Only YouTube exposes one by id. */
    public static function posterUrl(?string $url): ?string
    {
        $embed = static::embed($url);

        return ($embed['provider'] ?? null) === 'youtube'
            ? 'https://img.youtube.com/vi/'.$embed['id'].'/hqdefault.jpg'
            : null;
    }

    /** @param  list<string>  $extensions */
    private static function hasExtension(string $value, array $extensions): bool
    {
        $path = static::isExternal($value) ? (string) parse_url($value, PHP_URL_PATH) : $value;

        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $extensions, true);
    }
}
