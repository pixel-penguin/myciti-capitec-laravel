<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        Storage::disk('s3')->put($path, file_get_contents($request->file('photo')->getRealPath()), 'public');

        $user->update(['avatar_path' => $path]);

        return response()->json([
            'status' => 'uploaded',
            'avatar_url' => Storage::disk('s3')->url($path),
        ]);
    }
}
