<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PharmacyRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $pharmacyName;
    public string $reason;

    public function __construct(string $pharmacyName, string $reason)
    {
        $this->pharmacyName = $pharmacyName;
        $this->reason       = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'بخصوص طلب تسجيل صيدليتك في MedLink',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pharmacy-rejected',
        );
    }
}
