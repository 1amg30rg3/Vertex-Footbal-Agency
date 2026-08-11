<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUploader
{
    public const MAX_BYTES = 8 * 1024 * 1024;

    /** @var array<string, string> mime => extension */
    public const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/svg+xml' => 'svg',
    ];

    public static function store(mixed $value, string $folder, ?string $replacing = null): ?string
    {
        if ($value === null || $value === '' || $value === false) {
            static::forget($replacing);

            return null;
        }

        if ($value instanceof UploadedFile) {
            $path = static::storeUploadedFile($value, $folder);
            static::forgetIfChanged($replacing, $path);

            return $path;
        }

        if (is_string($value) && Str::startsWith($value, 'data:')) {
            $path = static::storeDataUri($value, $folder);
            static::forgetIfChanged($replacing, $path);

            return $path;
        }

        return is_string($value) ? $value : $replacing;
    }

    protected static function storeUploadedFile(UploadedFile $file, string $folder): string
    {
        $extension = static::ALLOWED[$file->getMimeType()] ?? null;

        abort_if($extension === null, 422, 'Unsupported image type.');
        abort_if($file->getSize() > static::MAX_BYTES, 422, 'Image is too large.');

        return $file->storeAs(
            trim($folder, '/'),
            static::filename($extension),
            ['disk' => static::disk()]
        );
    }

    protected static function storeDataUri(string $value, string $folder): ?string
    {
        if (! preg_match('#^data:(?<mime>[a-z0-9.+/-]+);base64,(?<data>.+)$#is', $value, $matches)) {
            return null;
        }

        $mime = strtolower($matches['mime']);
        $extension = static::ALLOWED[$mime] ?? null;

        abort_if($extension === null, 422, 'Unsupported image type.');

        $binary = base64_decode($matches['data'], true);

        abort_if($binary === false, 422, 'Malformed image payload.');
        abort_if(strlen($binary) > static::MAX_BYTES, 422, 'Image is too large.');
        abort_unless(static::looksLikeImage($binary, $mime), 422, 'File contents are not a valid image.');

        $path = trim($folder, '/').'/'.static::filename($extension);

        Storage::disk(static::disk())->put($path, $binary, 'public');

        return $path;
    }

    protected static function looksLikeImage(string $binary, string $mime): bool
    {
        return match ($mime) {
            'image/jpeg' => str_starts_with($binary, "\xFF\xD8\xFF"),
            'image/png' => str_starts_with($binary, "\x89PNG\r\n\x1a\n"),
            'image/gif' => str_starts_with($binary, 'GIF87a') || str_starts_with($binary, 'GIF89a'),
            'image/webp' => str_starts_with($binary, 'RIFF') && substr($binary, 8, 4) === 'WEBP',
            'image/svg+xml' => str_contains($binary, '<svg')
                && ! preg_match('/<script|on[a-z]+\s*=|javascript:/i', $binary),
            default => false,
        };
    }

    protected static function filename(string $extension): string
    {
        return now()->format('Ymd').'-'.Str::random(20).'.'.$extension;
    }

    public static function forget(?string $path): void
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return;
        }

        $disk = Storage::disk(static::disk());

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    protected static function forgetIfChanged(?string $old, ?string $new): void
    {
        if ($old !== null && $old !== $new) {
            static::forget($old);
        }
    }

    protected static function disk(): string
    {
        return config('filesystems.media_disk', 'public');
    }
}
