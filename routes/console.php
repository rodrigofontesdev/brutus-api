<?php

use Illuminate\Support\Facades\Log;
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
