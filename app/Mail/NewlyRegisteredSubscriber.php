<?php

namespace App\Mail;

use App\Models\MagicLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewlyRegisteredSubscriber extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $tries = 2;

    public function __construct(private MagicLink $magicLink)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bem-vindo(a) ao '.config('app.name').'! Acesse Sua Conta',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newly-registered-subscriber',
            with: ['link' => $this->magicLink->fullUrl()]
        );
    }
}
