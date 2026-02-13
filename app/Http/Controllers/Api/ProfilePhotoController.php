<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfilePhotoController extends Controller
{
    public function store(Request $request)
    {
        Log::info('ProfilePhoto::store called', [
            'has_file' => $request->hasFile('photo'),
            'all_files' => array_keys($request->allFiles()),
            'content_type' => $request->header('Content-Type'),
        ]);

        $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $file = $request->file('photo');
        $ext = $file->guessExtension() ?: 'jpg';
        $path = 'avatars/' . $user->id . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

        Log::info('ProfilePhoto: uploading to S3', [
            'path' => $path,
            'file_size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);

        $oldPath = $user->avatar_path;

        try {
            $uploaded = Storage::disk('s3')->put(
                $path,
                file_get_contents($file->getRealPath())
            );

            if (! $uploaded) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to upload file to storage.',
                ], 500);
            }
        } catch (\Throwable $e) {
            Log::error('S3 upload failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Storage upload failed: ' . $e->getMessage(),
            ], 500);
        }

        $user->update(['avatar_path' => $path]);

        // Delete the old avatar after successful upload
        if ($oldPath && $oldPath !== $path) {
            Storage::disk('s3')->delete($oldPath);
        }

        return response()->json([
            'status' => 'uploaded',
            'avatar_url' => self::avatarUrl($path),
        ]);
    }

    public static function avatarUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('s3')->url($path);
    }
}
