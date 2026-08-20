<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Support\MediaUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MediaUploadController extends Controller
{
    /** Stores one gallery video and returns its path. */
    public function video(Request $request): JsonResponse
    {
        $this->guardAgainstTruncatedUpload($request);

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimetypes:'.implode(',', array_keys(MediaUploader::ALLOWED_VIDEO)),
                'max:'.(int) (MediaUploader::MAX_VIDEO_BYTES / 1024),
            ],
        ], [
            'file.mimetypes' => 'Upload an MP4, WebM or MOV file.',
            'file.max' => 'That video is larger than '.(int) (MediaUploader::MAX_VIDEO_BYTES / 1024 / 1024).' MB.',
        ]);

        $path = MediaUploader::store($request->file('file'), 'players/gallery', video: true);

        return response()->json([
            'path' => $path,
            'url' => Player::mediaUrl($path),
        ]);
    }

    /** PHP drops the body when it exceeds post_max_size, leaving an empty request. */
    private function guardAgainstTruncatedUpload(Request $request): void
    {
        $length = (int) $request->server('CONTENT_LENGTH', 0);

        if ($length > 0 && $request->files->count() === 0 && $request->request->count() === 0) {
            throw ValidationException::withMessages([
                'file' => 'The upload was too large for this server. Raise upload_max_filesize and post_max_size in the hosting PHP settings, or use a YouTube/Vimeo link instead.',
            ]);
        }
    }
}
