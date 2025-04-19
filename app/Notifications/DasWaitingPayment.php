<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DasWaitingPayment extends Notification implements ShouldQueue
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
        $period = Carbon::now()->locale('pt_BR')->isoFormat('MMMM [de] Y');

        return (new MailMessage())
            ->subject('Seu DAS MEI Vence em Breve – Evite Multas!')
            ->markdown(
                'emails.das-waiting-payment',
                [
                    'firstName' => $notifiable->firstName,
                    'period' => Str::ucfirst($period),
                    'link' => 'https://www8.receita.fazenda.gov.br/SimplesNacional/Aplicacoes/ATSPO/pgmei.app/Identificacao',
                    'secretWord' => $notifiable->secret_word,
                ]
            );
    }
}
