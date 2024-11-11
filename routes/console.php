<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Stringable;

Schedule::command('model:prune')
    ->monthly()
    ->runInBackground()
    ->onSuccess(function(Stringable $output) {
        Log::info(
            'Schedule[model:prune]:: Finished pruning models.',
            ['output' => $output]
        );
    })
    ->onFailure(function(Stringable $output) {
        Log::warning(
            'Schedule[model:prune]:: Pruning models was not possible.',
            ['output' => $output]
        );
    });
