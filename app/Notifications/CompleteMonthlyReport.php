<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CompleteMonthlyReport extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $period = Carbon::now()->subMonth()->locale('pt_BR')->isoFormat('MMMM [de] Y');

        return (new MailMessage())
            ->subject('Não Esqueça de Criar o Relatório Mensal do MEI!')
            ->markdown(
                'emails.complete-monthly-report',
                [
                    'firstName' => $notifiable->firstName,
                    'period' => Str::ucfirst($period),
                    'link' => config('app.client.url'),
                    'secretWord' => $notifiable->secret_word,
                ]
            );
    }
}
