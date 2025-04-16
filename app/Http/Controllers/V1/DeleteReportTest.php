<?php

use App\Events\AnnualRevenueChanged;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

describe('Delete Report', function () {
    beforeEach(function () {
        $this->subscriber = User::factory()->create();
    });

    it('should return an unauthorized response for unauthenticated requests', function () {
        $reportId = ['id' => Str::uuid()->toString()];

        $response = $this->deleteJson(route('v1.reports.delete', $reportId));

        $response->assertUnauthorized();
    });

    it('should return a bad request if report ID is an invalid UUID', function () {
        $reportId = ['id' => 'invalid'];

        $response = $this->actingAs($this->subscriber)
            ->deleteJson(route('v1.reports.delete', $reportId));

        $response->assertBadRequest();
        $response->assertSee('The specified report ID in URL is invalid.');
    });

    it('should return a not found response for non-existent reports', function () {
        $reportId = ['id' => Str::uuid()->toString()];

        $response = $this->actingAs($this->subscriber)
            ->deleteJson(route('v1.reports.delete', $reportId));

        $response->assertNotFound();
    });

    it(
        'should return a forbidden response if the user attempts to delete another subscriber report',
        function () {
            $anotherSubscriber = User::factory()->has(Report::factory())->create();
            $reportId = ['id' => $anotherSubscriber->reports[0]->id];

            $response = $this->actingAs($this->subscriber)
                ->deleteJson(route('v1.reports.delete', $reportId));

            $response->assertForbidden();
        }
    );

    it('should delete report from the database', function () {
        $report = Report::factory()->for($this->subscriber, 'owner')->create();
        $reportId = ['id' => $report->id];

        $response = $this->actingAs($this->subscriber)
            ->deleteJson(route('v1.reports.delete', $reportId));

        $response->assertNoContent();
        $this->assertDatabaseMissing($report);
    });

    it('should dispatch an event to notify that the annual revenue has changed', function () {
        $report = Report::factory()->for($this->subscriber, 'owner')->create();
        $reportId = ['id' => $report->id];

        Event::fake();

        $this->actingAs($this->subscriber)->deleteJson(route('v1.reports.delete', $reportId));

        Event::assertDispatched(AnnualRevenueChanged::class);
    });
});
