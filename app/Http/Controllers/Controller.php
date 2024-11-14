<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class Controller
{
    public function __construct()
    {
        Log::withContext([
            'context_id' => Str::ulid()->toString(),
            'request' => [
                'path' => request()->path(),
                'method' => request()->method(),
                'body' => request()->except(['email', 'secret_word']),
            ],
        ]);
    }
}
