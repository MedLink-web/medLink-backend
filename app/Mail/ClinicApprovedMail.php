<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClinicApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $clinicName;
    public string $email;
    public string $password;

    public function __construct(string $clinicName, string $email, string $password)
    {
        $this->clinicName = $clinicName;
        $this->email      = $email;
        $this->password   = $password;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تم قبول طلب تسجيل عيادتك في MedLink',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.clinic-approved',
        );
    }
}
