<?php

use App\Models\User;
use App\Notifications\CompleteMonthlyReport;
use Illuminate\Support\Facades\Notification;

describe('Complete Monthly Report', function() {
    beforeEach(function () {
        $this->subscribers = User::factory()->count(10)->create();
    });

    it('should remind subscribers with free plan to fill out the monthly report', function() {
        Notification::fake();

        Notification::sendNow($this->subscribers, new CompleteMonthlyReport());

        Notification::assertSentTo($this->subscribers, CompleteMonthlyReport::class);
    });
});
