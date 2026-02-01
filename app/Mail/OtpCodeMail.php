<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public int $minutes
    ) {
    }

    public function build()
    {
        return $this->subject('Your Capitec Shuttle OTP')
            ->view('emails.otp-code');
    }
}
