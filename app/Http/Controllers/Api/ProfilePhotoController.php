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
        $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $path = 'avatars/' . $user->id . '.jpg';

        try {
            $uploaded = Storage::disk('s3')->put(
                $path,
                file_get_contents($request->file('photo')->getRealPath()),
                'public'
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

        return response()->json([
            'status' => 'uploaded',
            'avatar_url' => Storage::disk('s3')->url($path),
        ]);
    }
}
