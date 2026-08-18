<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Streams admin-uploaded media from the "public" disk.
 *
 * Shared hosting without shell access cannot run `php artisan storage:link`,
 * so public/storage may be missing (or be a symlink Apache refuses to follow,
 * which answers 403). The root .htaccess falls through to this route whenever
 * no real file is there, so media resolves through PHP instead. Cache headers
 * are set explicitly because upload filenames are unique per upload, which the
 * framework's own `serve` route cannot assume (it sends no-store).
 */
class MediaController extends Controller
{
    public function __invoke(Request $request, string $path): Response
    {
        $disk = Storage::disk('public');

        $root = realpath($disk->path(''));
        $file = realpath($disk->path($path));

        // realpath() collapses ../, so a traversal attempt resolves outside the
        // disk root (or to false) and is rejected here rather than served.
        abort_if($root === false || $file === false, 404);
        abort_unless(str_starts_with($file, rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR), 404);
        abort_unless(is_file($file) && is_readable($file), 404);

        $response = new BinaryFileResponse($file, 200, [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        $response->setAutoLastModified();
        $response->isNotModified($request);

        return $response;
    }
}
