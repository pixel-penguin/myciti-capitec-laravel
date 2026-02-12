<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OtpCodeMail;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TwoFactorController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // Auto-send OTP if none sent recently (within last 2 minutes)
        $recentOtp = OtpCode::query()
            ->where('email', $user->email)
            ->whereNull('consumed_at')
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();

        if (! $recentOtp) {
            $this->sendOtp($user);
            return redirect()->route('admin.two-factor.challenge')
                ->with('status', 'A verification code has been sent to your email.');
        }

        return view('admin.two-factor.challenge');
    }

    public function send(Request $request)
    {
        $this->sendOtp($request->user());

        return redirect()->route('admin.two-factor.challenge')
            ->with('status', 'OTP sent to your email.');
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();

        $otp = OtpCode::query()
            ->where('email', $user->email)
            ->whereNull('consumed_at')
            ->orderByDesc('id')
            ->first();

        if (! $otp || $otp->expires_at->isPast()) {
            return redirect()->back()->withErrors(['code' => 'OTP expired.']);
        }

        if (! Hash::check($data['code'], $otp->code_hash)) {
            $otp->increment('attempts');

            return redirect()->back()->withErrors(['code' => 'Invalid code.']);
        }

        $otp->update(['consumed_at' => now()]);

        $request->session()->put('two_factor_passed', true);
        $user->update(['two_factor_confirmed_at' => now()]);

        return redirect()->route('admin.dashboard');
    }

    private function sendOtp($user): void
    {
        $code = (string) random_int(100000, 999999);
        $minutes = 10;

        OtpCode::create([
            'email' => $user->email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes($minutes),
        ]);

        Mail::to($user->email)->send(new OtpCodeMail($code, $minutes));
    }
}
