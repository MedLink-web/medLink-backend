<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PharmacyApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $pharmacyName;
    public string $email;
    public string $password;

    public function __construct(string $pharmacyName, string $email, string $password)
    {
        $this->pharmacyName = $pharmacyName;
        $this->email        = $email;
        $this->password     = $password;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تم قبول طلب تسجيل صيدليتك في MedLink',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pharmacy-approved',
        );
    }
}
