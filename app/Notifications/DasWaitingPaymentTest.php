<?php

use App\Models\User;
use App\Notifications\DasWaitingPayment;
use Illuminate\Support\Facades\Notification;

describe('DAS Waiting Payment', function() {
    beforeEach(function () {
        $this->subscribers = User::factory()->count(10)->create();
    });

    it('should remind subscribers with free plan to pay the DAS', function() {
        Notification::fake();

        Notification::sendNow($this->subscribers, new DasWaitingPayment());

        Notification::assertSentTo($this->subscribers, DasWaitingPayment::class);
    });
});
