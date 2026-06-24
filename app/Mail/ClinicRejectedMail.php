<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClinicRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $clinicName;
    public string $reason;

    public function __construct(string $clinicName, string $reason)
    {
        $this->clinicName = $clinicName;
        $this->reason     = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'بخصوص طلب تسجيل عيادتك في MedLink',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.clinic-rejected',
        );
    }
}
