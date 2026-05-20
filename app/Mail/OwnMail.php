<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Utilities\VehicleRentalUtilities;
use App\Models\Passcode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Mail\Mailables\Address;

class OwnMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $emailTemplates;
    public $emailSubject;
    public $companyName;
    public $headerColor;
    public $supportEmail;
    public $log;
    public $name;
    public $response;
    public $emailContent;
    public $personalDetails;
    public $passcode;
    public $currentUserId;
    public $ccs;

    public function __construct($mailType, $emailTemplates, $personalDetails, $response, $activites)
    {
        $this->currentUserId = Auth::user() ? Auth::user()->id : 0;
        if ($mailType == 'User') {
            $userDetailId = $personalDetails['id'];
            $this->companyName = 'Kathmandu Sightseeing';
            $this->supportEmail = 'https://kathmandusightseeing.com/#';
            $this->log = 'https://kathmandusightseeing.com/upload/landing/da7/kk80s8bh77k7feeu0h1nlrbgcx09hgj0/sightseeing_1x.png';
        }

        if ($activites == 'passcode' || $activites == 'forgot_password' || $activites == 'password_reset_otp') {
            $this->passcode  = Passcode::where('email', $personalDetails['email'])->latest()->first();
        }

        if ($response == 'success') {
            $this->emailContent =  VehicleRentalUtilities::searchForEmailVar($emailTemplates->success_email_content, $personalDetails, $this->passcode);
        } else {
            $this->emailContent = $emailTemplates->emailTemplate->error_email_content;
        }

        $this->personalDetails = $personalDetails;


        $this->emailSubject = $emailTemplates->email_subject;
        $this->ccs = $emailTemplates->email_cc;

        $this->name = trim(
            (
                (is_array($personalDetails) ? ($personalDetails['first_name'] ?? '') : ($personalDetails->first_name ?? '')) . ' ' .
                (is_array($personalDetails) ? ($personalDetails['last_name'] ?? '') : ($personalDetails->last_name ?? ''))
            ) ?: (is_array($personalDetails) ? ($personalDetails['benef_name'] ?? null) : ($personalDetails->benef_name ?? null))
        );

        $this->response = $response;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $ccs = $this->ccs ?? ''; // Assuming $ccs is a comma-separated string

        // Convert to array and trim whitespace
        $ccArray = array_filter(array_map('trim', explode(',', $ccs)));

        return new Envelope(
            subject: $this->emailSubject,
            bcc: !empty($ccArray) ? collect($ccArray)->map(function ($cc) {
                return new Address($cc);
            })->all() : []
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ownmail',
            with: ['companyName' => $this->companyName, 'name' => $this->name, 'content' => $this->emailContent, 'supportEmail' => $this->supportEmail, 'logo' => $this->log, 'headerColor' => $this->headerColor]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
