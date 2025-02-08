<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DasWaitingPayment extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
                    ->subject('Seu DAS MEI vence em breve – Evite multas!')
                    ->greeting('Olá,')
                    ->line('The introduction to the notification.')
                    ->line('Thank you for using our application!');
    }
}
