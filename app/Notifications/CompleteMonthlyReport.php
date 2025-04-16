<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompleteMonthlyReport extends Notification implements ShouldQueue
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
        return (new MailMessage())
                    ->subject('Não Deixe Seu Negócio Em Risco! Envie o Relatório Mensal')
                    ->greeting('Olá,')
                    ->line('The introduction to the notification.')
                    ->action('Preencher Relatório Mensal', config('app.client.url'))
                    ->line('Thank you for using our application!');
    }
}
