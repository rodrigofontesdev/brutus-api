<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuthenticateWithMagickLink extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private string $link, private string $secretWord) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Access Your Account',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.authenticate-with-magic-link',
            with: [
                'link' => $this->link,
                'secretWord' => $this->secretWord
            ]
        );
    }
}
