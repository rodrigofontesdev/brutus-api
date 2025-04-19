<?php

use App\Models\User;
use App\Notifications\CompleteMonthlyReport;
use App\Notifications\DasWaitingPayment;
use App\Notifications\SendDasnSimeiStatement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Stringable;

Schedule::command('model:prune')
    ->monthly()
    ->runInBackground()
    ->onSuccess(function (Stringable $output) {
        Log::info(
            'Schedule[model:prune]:: Finished pruning models.',
            ['output' => $output]
        );
    })
    ->onFailure(function (Stringable $output) {
        Log::warning(
            'Schedule[model:prune]:: Pruning models was not possible.',
            ['output' => $output]
        );
    });

Schedule::command('queue:prune-failed --hours=72')
    ->monthly()
    ->runInBackground()
    ->onSuccess(function (Stringable $output) {
        Log::info(
            'Schedule[queue:prune-failed]:: Finished pruning failed jobs.',
            ['output' => $output]
        );
    })
    ->onFailure(function (Stringable $output) {
        Log::warning(
            'Schedule[queue:prune-failed]:: Pruning failed jobs was not possible.',
            ['output' => $output]
        );
    });

Schedule::command('queue:monitor database:default --max=100')
    ->everyMinute()
    ->onFailure(function (Stringable $output) {
        Log::warning(
            'Schedule[queue:monitor]:: Monitoring queues was not possible.',
            ['output' => $output]
        );
    });

Schedule::call(function () {
    User::subscriber()->chunk(500, function (Collection $subscribers) {
        Notification::send($subscribers, new CompleteMonthlyReport());
    });
})
->monthly()
->mondays()
->at('16:00')
->onFailure(function (Stringable $output) {
    Log::warning(
        'Schedule:: Remind subscribers to fill out the monthly report was not possible.',
        ['output' => $output]
    );
});

Schedule::call(function () {
    User::subscriber()->chunk(500, function (Collection $subscribers) {
        Notification::send($subscribers, new DasWaitingPayment());
    });
})
->monthlyOn(10, '07:00')
->onFailure(function (Stringable $output) {
    Log::warning(
        'Schedule:: Remind subscribers to pay the DAS was not possible.',
        ['output' => $output]
    );
});

Schedule::call(function () {
    User::subscriber()->chunk(500, function (Collection $subscribers) {
        Notification::send($subscribers, new SendDasnSimeiStatement());
    });
})
->yearlyOn(2, 1, '07:00')
->onFailure(function (Stringable $output) {
    Log::warning(
        'Schedule:: Remind subscribers to send the DASN-SIMEI was not possible.',
        ['output' => $output]
    );
});
