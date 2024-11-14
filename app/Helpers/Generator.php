<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class Generator
{
    public static function magicLinkExpireTime(): string
    {
        return Carbon::now()->addMinutes(15)->toDateTimeString();
    }
}
