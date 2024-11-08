<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewlyRegisteredSubscriber extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(private string $link)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Created Successfuly',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newly-registered-subscriber',
            with: ['link' => $this->link]
        );
    }
}
