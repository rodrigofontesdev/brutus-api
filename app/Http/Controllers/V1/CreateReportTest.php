<?php

use App\Events\AnnualRevenueChanged;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

describe('Create Report', function() {
    beforeEach(function () {
        $this->subscriber = User::factory()->create();
    });

    it('should return an unauthorized response for unauthenticated requests', function () {
        $response = $this->postJson(route('v1.reports.create'), []);

        $response->assertUnauthorized();
    });

    it('should return a bad request if missing required fields', function () {
        $response = $this->actingAs($this->subscriber)->postJson(route('v1.reports.create'), []);

        $response->assertBadRequest();
        $response->assertSee([
            'The trade with invoice field must be present.',
            'The trade without invoice field must be present.',
            'The industry with invoice field must be present.',
            'The industry without invoice field must be present.',
            'The services with invoice field must be present.',
            'The services without invoice field must be present.',
            'The period field is required.',
        ]);
    });

    it('should return a bad request if trade with invoice field is an invalid amount',
        function () {
            $payload = ['trade_with_invoice' => 1000.99];

            $response = $this->actingAs($this->subscriber)
                ->postJson(route('v1.reports.create'), $payload);

            $response->assertBadRequest();
            $response->assertSee('The trade with invoice field must be an integer.');
        }
    );

    it('should return a bad request if trade without invoice field is an invalid amount',
        function () {
            $payload = ['trade_without_invoice' => 1000.99];

            $response = $this->actingAs($this->subscriber)
                ->postJson(route('v1.reports.create'), $payload);

            $response->assertBadRequest();
            $response->assertSee('The trade without invoice field must be an integer.');
        }
    );

    it('should return a bad request if industry with invoice field is an invalid amount',
        function () {
            $payload = ['industry_with_invoice' => 1000.99];

            $response = $this->actingAs($this->subscriber)
                ->postJson(route('v1.reports.create'), $payload);

            $response->assertBadRequest();
            $response->assertSee('The industry with invoice field must be an integer.');
        }
    );

    it('should return a bad request if industry without invoice field is an invalid amount',
        function () {
            $payload = ['industry_without_invoice' => 1000.99];

            $response = $this->actingAs($this->subscriber)
                ->postJson(route('v1.reports.create'), $payload);

            $response->assertBadRequest();
            $response->assertSee('The industry without invoice field must be an integer.');
        }
    );

    it('should return a bad request if services with invoice field is an invalid amount',
        function () {
            $payload = ['services_with_invoice' => 1000.99];

            $response = $this->actingAs($this->subscriber)
                ->postJson(route('v1.reports.create'), $payload);

            $response->assertBadRequest();
            $response->assertSee('The services with invoice field must be an integer.');
        }
    );

    it('should return a bad request if services without invoice field is an invalid amount',
        function () {
            $payload = ['services_without_invoice' => 1000.99];

            $response = $this->actingAs($this->subscriber)
                ->postJson(route('v1.reports.create'), $payload);

            $response->assertBadRequest();
            $response->assertSee('The services without invoice field must be an integer.');
        }
    );

    it('should return a bad request if the period field is an invalid date',
        function (string $period) {
            $payload = [
                'trade_with_invoice' => 100000,
                'trade_without_invoice' => 50000,
                'industry_with_invoice' => 50000,
                'industry_without_invoice' => 200000,
                'services_with_invoice' => 0,
                'services_without_invoice' => 0,
                'period' => $period
            ];

            $response = $this->actingAs($this->subscriber)
                ->postJson(route('v1.reports.create'), $payload);

            $response->assertBadRequest();
            $response->assertSee('The period field must match the format Y-m-d.');
        }
    )->with(['January 1, 2025', '01-01-2025', '01/01/2025']);

    it('should return a bad request if the user attempts to create a report for a period that already exists',
        function() {
            $report = Report::factory()->for($this->subscriber, 'owner')
                ->onlyTradeInvoice()
                ->state(['period' => '2025-01-01'])
                ->create();
            $payload = [
                'trade_with_invoice' => 100000,
                'trade_without_invoice' => 50000,
                'industry_with_invoice' => 50000,
                'industry_without_invoice' => 200000,
                'services_with_invoice' => 0,
                'services_without_invoice' => 0,
                'period' => $report->period
            ];

            $response = $this->actingAs($this->subscriber)
                ->postJson(route('v1.reports.create'), $payload);

            $response->assertBadRequest();
            $response->assertSee('A report for the specified period already exists.');
        }
    );

    it('should create the report in the database if the fields are valid', function() {
        $payload = [
            'trade_with_invoice' => 100000,
            'trade_without_invoice' => 50000,
            'industry_with_invoice' => 50000,
            'industry_without_invoice' => 200000,
            'services_with_invoice' => 0,
            'services_without_invoice' => 0,
            'period' => '2025-01-01'
        ];

        $response = $this->actingAs($this->subscriber)
                ->postJson(route('v1.reports.create'), $payload);

        $reportCreated = Report::find($response['id']);

        $this->assertModelExists($reportCreated);
    });

    it('should dispatch an event to notify that the annual revenue has changed', function() {
        $payload = [
            'trade_with_invoice' => 100000,
            'trade_without_invoice' => 50000,
            'industry_with_invoice' => 50000,
            'industry_without_invoice' => 200000,
            'services_with_invoice' => 0,
            'services_without_invoice' => 0,
            'period' => '2025-01-01'
        ];

        Event::fake();

        $this->actingAs($this->subscriber)->postJson(route('v1.reports.create'), $payload);

        Event::assertDispatched(AnnualRevenueChanged::class);
    });

    it('should return a successful response if the report is created', function () {
        $payload = [
            'trade_with_invoice' => 100000,
            'trade_without_invoice' => 50000,
            'industry_with_invoice' => 50000,
            'industry_without_invoice' => 200000,
            'services_with_invoice' => 0,
            'services_without_invoice' => 0,
            'period' => '2025-01-01'
        ];

        $response = $this->actingAs($this->subscriber)
            ->postJson(route('v1.reports.create'), $payload);

        $response->assertCreated();
        $response->assertJson(
            fn (AssertableJson $json) => $json->where('user', $this->subscriber->id)
                ->where('period', '2025-01-01')
                ->etc()
        );
    });
});
