<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'department' => $user->department,
                'avatar_url' => $user->avatar_path
                    ? \Illuminate\Support\Facades\Storage::disk('s3')->url($user->avatar_path)
                    : null,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['required', 'string', 'max:100'],
            'department' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $user->update($data);

        return response()->json([
            'status' => 'updated',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'department' => $user->department,
                'avatar_url' => $user->avatar_path
                    ? \Illuminate\Support\Facades\Storage::disk('s3')->url($user->avatar_path)
                    : null,
            ],
        ]);
    }
}
