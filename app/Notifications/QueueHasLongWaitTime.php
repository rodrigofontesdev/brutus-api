<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Events\QueueBusy;

class QueueHasLongWaitTime extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private QueueBusy $event)
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
            ->subject('Queue Monitoring Alert')
            ->greeting('System Message')
            ->line('We\'re monitoring exceeded queues and have identified an alert:')
            ->line('**Queue Connection:** '.$this->event->connection)
            ->line('**Queue Name:** '.$this->event->queue)
            ->line('**Queue Size:** '.$this->event->size);
    }
}
