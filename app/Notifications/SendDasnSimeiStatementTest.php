<?php

use App\Models\User;
use App\Notifications\SendDasnSimeiStatement;
use Illuminate\Support\Facades\Notification;

describe('Send DASN SIMEI Statement', function() {
    beforeEach(function () {
        $this->subscribers = User::factory()->count(10)->create();
    });

    it('should remind subscribers with free plan to send the DASN SIMEI Statement', function() {
        Notification::fake();

        Notification::sendNow($this->subscribers, new SendDasnSimeiStatement());

        Notification::assertSentTo($this->subscribers, SendDasnSimeiStatement::class);
    });
});
