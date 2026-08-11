<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @mixin Model
 */
trait HasMedia
{
    public static function mediaUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return $path;
        }

        return Storage::disk(config('filesystems.media_disk', 'public'))->url($path);
    }

    public function urlFor(string $attribute): ?string
    {
        return static::mediaUrl($this->getAttribute($attribute));
    }

    /**
     * Absolute URL, for anywhere a relative path is not good enough — og:image
     * tags, feeds and mail.
     */
    public static function absoluteMediaUrl(?string $path): ?string
    {
        $url = static::mediaUrl($path);

        if ($url === null || Str::startsWith($url, ['http://', 'https://', '//'])) {
            return $url;
        }

        return url($url);
    }

    public static function deleteMedia(?string $path): void
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return;
        }

        $disk = Storage::disk(config('filesystems.media_disk', 'public'));

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
