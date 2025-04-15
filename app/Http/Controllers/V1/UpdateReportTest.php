<?php

use App\Events\AnnualRevenueChanged;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

describe('Update Report', function() {
    beforeEach(function () {
        $this->subscriber = User::factory()->has(Report::factory())->create();
    });

    it('should return an unauthorized response for unauthenticated requests', function () {
        $reportId = ['id' => Str::uuid()->toString()];

        $response = $this->putJson(route('v1.reports.update', $reportId));

        $response->assertUnauthorized();
    });

    it('should return a bad request if report ID is an invalid UUID', function () {
        $reportId = ['id' => 'invalid'];
        $payload = [
            'trade_with_invoice' => 100000,
            'trade_without_invoice' => 0,
            'industry_with_invoice' => 0,
            'industry_without_invoice' => 0,
            'services_with_invoice' => 0,
            'services_without_invoice' => 0,
        ];

        $response = $this->actingAs($this->subscriber)
            ->putJson(route('v1.reports.update', $reportId), $payload);

        $response->assertBadRequest();
        $response->assertSee('The specified report ID in URL is invalid.');
    });

    it('should return a not found response for non-existent reports', function () {
        $reportId = ['id' => Str::uuid()->toString()];
        $payload = [
            'trade_with_invoice' => 100000,
            'trade_without_invoice' => 0,
            'industry_with_invoice' => 0,
            'industry_without_invoice' => 0,
            'services_with_invoice' => 0,
            'services_without_invoice' => 0,
        ];

        $response = $this->actingAs($this->subscriber)
            ->putJson(route('v1.reports.update', $reportId), $payload);

        $response->assertNotFound();
    });

    it(
        'should return a forbidden response if the user attempts to obtain another subscriber report',
        function () {
            $anotherSubscriber = User::factory()->has(Report::factory())->create();
            $reportId = ['id' => $anotherSubscriber->reports[0]->id];
            $payload = [
                'trade_with_invoice' => 100000,
                'trade_without_invoice' => 0,
                'industry_with_invoice' => 0,
                'industry_without_invoice' => 0,
                'services_with_invoice' => 0,
                'services_without_invoice' => 0,
            ];

            $response = $this->actingAs($this->subscriber)
                ->putJson(route('v1.reports.update', $reportId), $payload);

            $response->assertForbidden();
        }
    );

    it('should return a bad request if missing required fields', function () {
        $reportId = ['id' => $this->subscriber->reports[0]->id];

        $response = $this->actingAs($this->subscriber)
            ->putJson(route('v1.reports.update', $reportId), []);

        $response->assertBadRequest();
        $response->assertSee([
            'The trade with invoice field must be present.',
            'The trade without invoice field must be present.',
            'The industry with invoice field must be present.',
            'The industry without invoice field must be present.',
            'The services with invoice field must be present.',
            'The services without invoice field must be present.',
        ]);
    });

    it('should return a bad request if trade with invoice field is an invalid amount',
        function () {
            $reportId = ['id' => $this->subscriber->reports[0]->id];
            $payload = ['trade_with_invoice' => 1000.99];

            $response = $this->actingAs($this->subscriber)
                ->putJson(route('v1.reports.update', $reportId), $payload);

            $response->assertBadRequest();
            $response->assertSee('The trade with invoice field must be an integer.');
        }
    );

    it('should return a bad request if trade without invoice field is an invalid amount',
        function () {
            $reportId = ['id' => $this->subscriber->reports[0]->id];
            $payload = ['trade_without_invoice' => 1000.99];

            $response = $this->actingAs($this->subscriber)
                ->putJson(route('v1.reports.update', $reportId), $payload);

            $response->assertBadRequest();
            $response->assertSee('The trade without invoice field must be an integer.');
        }
    );

    it('should return a bad request if industry with invoice field is an invalid amount',
        function () {
            $reportId = ['id' => $this->subscriber->reports[0]->id];
            $payload = ['industry_with_invoice' => 1000.99];

            $response = $this->actingAs($this->subscriber)
                ->putJson(route('v1.reports.update', $reportId), $payload);

            $response->assertBadRequest();
            $response->assertSee('The industry with invoice field must be an integer.');
        }
    );

    it('should return a bad request if industry without invoice field is an invalid amount',
        function () {
            $reportId = ['id' => $this->subscriber->reports[0]->id];
            $payload = ['industry_without_invoice' => 1000.99];

            $response = $this->actingAs($this->subscriber)
                ->putJson(route('v1.reports.update', $reportId), $payload);

            $response->assertBadRequest();
            $response->assertSee('The industry without invoice field must be an integer.');
        }
    );

    it('should return a bad request if services with invoice field is an invalid amount',
        function () {
            $reportId = ['id' => $this->subscriber->reports[0]->id];
            $payload = ['services_with_invoice' => 1000.99];

            $response = $this->actingAs($this->subscriber)
                ->putJson(route('v1.reports.update', $reportId), $payload);

            $response->assertBadRequest();
            $response->assertSee('The services with invoice field must be an integer.');
        }
    );

    it('should return a bad request if services without invoice field is an invalid amount',
        function () {
            $reportId = ['id' => $this->subscriber->reports[0]->id];
            $payload = ['services_without_invoice' => 1000.99];

            $response = $this->actingAs($this->subscriber)
                ->putJson(route('v1.reports.update', $reportId), $payload);

            $response->assertBadRequest();
            $response->assertSee('The services without invoice field must be an integer.');
        }
    );

    it('should update report in the database', function() {
        $reportId = ['id' => $this->subscriber->reports[0]->id];
        $payload = [
            'trade_with_invoice' => 100000,
            'trade_without_invoice' => 50000,
            'industry_with_invoice' => 50000,
            'industry_without_invoice' => 200000,
            'services_with_invoice' => 0,
            'services_without_invoice' => 0,
        ];

        $response = $this->actingAs($this->subscriber)
            ->putJson(route('v1.reports.update', $reportId), $payload);

        $updatedReport = Report::find($reportId)->first();

        $response->assertOk();
        $response->assertJson($updatedReport->toArray());
        $this->assertModelExists($updatedReport);
    });

    it('should dispatch an event to notify that the annual revenue has changed', function() {
        Event::fake();
        $reportId = ['id' => $this->subscriber->reports[0]->id];
        $payload = [
            'trade_with_invoice' => 100000,
            'trade_without_invoice' => 50000,
            'industry_with_invoice' => 50000,
            'industry_without_invoice' => 200000,
            'services_with_invoice' => 0,
            'services_without_invoice' => 0,
        ];

        $response = $this->actingAs($this->subscriber)
            ->putJson(route('v1.reports.update', $reportId), $payload);

        Event::assertDispatched(AnnualRevenueChanged::class);
        $response->assertOk();
    });
});
