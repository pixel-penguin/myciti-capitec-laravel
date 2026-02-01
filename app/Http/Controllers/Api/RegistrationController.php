<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpCodeMail;
use App\Models\EmployeeEligibility;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function requestOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $eligibility = EmployeeEligibility::where('email', $data['email'])->first();
        if (! $eligibility || $eligibility->status !== 'active') {
            return response()->json([
                'status' => 'not_eligible',
                'message' => 'Email not eligible for registration.',
            ], 403);
        }

        $code = (string) random_int(100000, 999999);
        $minutes = 10;

        OtpCode::create([
            'email' => $data['email'],
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes($minutes),
        ]);

        Mail::to($data['email'])->send(new OtpCodeMail($code, $minutes));

        return response()->json([
            'status' => 'sent',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $eligibility = EmployeeEligibility::where('email', $data['email'])->first();
        if (! $eligibility || $eligibility->status !== 'active') {
            return response()->json([
                'status' => 'not_eligible',
            ], 403);
        }

        $otp = OtpCode::query()
            ->where('email', $data['email'])
            ->whereNull('consumed_at')
            ->orderByDesc('id')
            ->first();

        if (! $otp || $otp->expires_at->isPast()) {
            return response()->json([
                'status' => 'expired',
            ], 422);
        }

        if (! Hash::check($data['code'], $otp->code_hash)) {
            $otp->increment('attempts');

            return response()->json([
                'status' => 'invalid_code',
            ], 422);
        }

        $otp->update(['consumed_at' => now()]);

        $user = User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'status' => 'active',
                'employee_eligibility_id' => $eligibility->id,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(32)),
            ]
        );

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'status' => 'verified',
            'user_id' => $user->id,
            'token' => $token,
        ]);
    }
}
