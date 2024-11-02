<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmAccountCreated extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(private string $link)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirm Account Created',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.confirm-account-created',
            with: ['link' => $this->link]
        );
    }
}
