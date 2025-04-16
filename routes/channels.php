<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('annual-revenue.{subscriberId}', function (User $user, string $subscriberId) {
    return $user->id === $subscriberId && $user->isSubscriber();
});
