<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendDasnSimeiStatement extends Notification implements ShouldQueue
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
        $period = today()->subYear()->format('Y');

        return (new MailMessage())
            ->subject('Prazo Final Para Entregar o DASN-SIMEI de '.$period.' se Aproxima!')
            ->markdown(
                'emails.send-dasn-simei-statement',
                [
                    'firstName' => $notifiable->firstName,
                    'period' => $period,
                    'link' => 'https://www8.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/dasnsimei.app/Identificacao',
                    'secretWord' => $notifiable->secret_word,
                ]
            );
    }
}
