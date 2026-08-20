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

    public const MAX_VIDEO_BYTES = 100 * 1024 * 1024;

    /** @var array<string, string> mime => extension */
    public const ALLOWED_VIDEO = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
    ];

    public static function store(mixed $value, string $folder, ?string $replacing = null, bool $video = false): ?string
    {
        if ($value === null || $value === '' || $value === false) {
            static::forget($replacing);

            return null;
        }

        if ($value instanceof UploadedFile) {
            $path = static::storeUploadedFile($value, $folder, $video);
            static::forgetIfChanged($replacing, $path);

            return $path;
        }

        if (is_string($value) && Str::startsWith($value, 'data:')) {
            $path = static::storeDataUri($value, $folder, $video);
            static::forgetIfChanged($replacing, $path);

            return $path;
        }

        if (is_string($value)) {
            static::forgetIfChanged($replacing, $value);

            return $value;
        }

        return $replacing;
    }

    protected static function storeUploadedFile(UploadedFile $file, string $folder, bool $video = false): string
    {
        $mime = (string) $file->getMimeType();
        $allowed = $video ? static::ALLOWED + static::ALLOWED_VIDEO : static::ALLOWED;
        $extension = $allowed[$mime] ?? null;

        abort_if($extension === null, 422, $video ? 'Unsupported image or video type.' : 'Unsupported image type.');

        $isVideo = isset(static::ALLOWED_VIDEO[$mime]);
        $limit = $isVideo ? static::MAX_VIDEO_BYTES : static::MAX_BYTES;

        abort_if($file->getSize() > $limit, 422, $isVideo ? 'Video is too large.' : 'Image is too large.');

        return $file->storeAs(
            trim($folder, '/'),
            static::filename($extension),
            ['disk' => static::disk()]
        );
    }

    protected static function storeDataUri(string $value, string $folder, bool $video = false): ?string
    {
        if (! preg_match('#^data:(?<mime>[a-z0-9.+/-]+);base64,(?<data>.+)$#is', $value, $matches)) {
            return null;
        }

        $mime = strtolower($matches['mime']);
        $allowed = $video ? static::ALLOWED + static::ALLOWED_VIDEO : static::ALLOWED;
        $extension = $allowed[$mime] ?? null;

        abort_if($extension === null, 422, $video ? 'Unsupported image or video type.' : 'Unsupported image type.');

        $binary = base64_decode($matches['data'], true);

        abort_if($binary === false, 422, 'Malformed media payload.');

        $isVideo = isset(static::ALLOWED_VIDEO[$mime]);

        abort_if(strlen($binary) > ($isVideo ? static::MAX_VIDEO_BYTES : static::MAX_BYTES), 422, $isVideo ? 'Video is too large.' : 'Image is too large.');
        abort_unless(
            $isVideo ? static::looksLikeVideo($binary, $mime) : static::looksLikeImage($binary, $mime),
            422,
            'File contents do not match the declared type.'
        );

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

    protected static function looksLikeVideo(string $binary, string $mime): bool
    {
        return match ($mime) {
            'video/mp4', 'video/quicktime' => substr($binary, 4, 4) === 'ftyp',
            'video/webm' => str_starts_with($binary, "\x1A\x45\xDF\xA3"),
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
