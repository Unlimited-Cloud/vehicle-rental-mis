<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginOtpMail extends Mailable
{
    public string $otp;
    public string $name;

    public function __construct($otp, string $name)
    {
        $this->otp = $otp;
        $this->name = $name;
    }

    public function build()
    {
        return $this->subject('Your OTP Code')
            ->view('emails.otp.passcode')
            ->with([
                'otp'  => $this->otp,
                'name' => $this->name,
            ]);
    }
}
