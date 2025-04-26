<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuthenticateWithMagickLink extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $tries = 2;

    public function __construct(private User $subscriber)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Seu Link Para Acessar o '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.authenticate-with-magic-link',
            with: [
                'link' => $this->subscriber->latestMagicLink->fullUrl(),
                'secretWord' => $this->subscriber->secret_word,
            ]
        );
    }
}
