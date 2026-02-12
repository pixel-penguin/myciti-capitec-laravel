<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfilePhotoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $filename = $user->id . '.jpg';

        $request->file('photo')->storeAs('public/avatars', $filename);

        $user->update(['avatar_path' => 'avatars/' . $filename]);

        return response()->json([
            'status' => 'uploaded',
            'avatar_path' => $user->avatar_path,
        ]);
    }
}
