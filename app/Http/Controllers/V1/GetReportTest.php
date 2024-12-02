<?php

use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Str;

describe('Get Report', function () {
    beforeEach(function () {
        $this->subscriber = User::factory()->create();
    });

    it('should return an unauthorized response for unauthenticated requests', function () {
        $reportId = ['id' => Str::uuid()->toString()];

        $response = $this->getJson(route('v1.reports.show', $reportId));

        $response->assertUnauthorized();
    });

    it('should return a bad request if report ID is an invalid UUID', function () {
        $reportId = ['id' => 'invalid'];

        $response = $this->actingAs($this->subscriber)
            ->getJson(route('v1.reports.show', $reportId));

        $response->assertBadRequest();
        $response->assertSee('The specified report ID in URL is invalid.');
    });

    it('should return a not found response for non-existent reports', function () {
        $reportId = ['id' => Str::uuid()->toString()];

        $response = $this->actingAs($this->subscriber)
            ->getJson(route('v1.reports.show', $reportId));

        $response->assertNotFound();
    });

    it(
        'should return a forbidden response if the user attempts to obtain another subscriber report',
        function () {
            $anotherSubscriber = User::factory()->has(Report::factory())->create();
            $reportId = ['id' => $anotherSubscriber->reports[0]->id];

            $response = $this->actingAs($this->subscriber)
                ->getJson(route('v1.reports.show', $reportId));

            $response->assertForbidden();
        }
    );

    it('should return the requested report', function () {
        $report = Report::factory()->state(['user' => $this->subscriber->id])->create();
        $reportId = ['id' => $report->id];

        $response = $this->actingAs($this->subscriber)
            ->getJson(route('v1.reports.show', $reportId));

        $response->assertOk();
        $response->assertJson($report->toArray());
    });
});
