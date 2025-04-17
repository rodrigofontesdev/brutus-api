<?php

use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;

describe('Get Reports', function () {
    beforeEach(function () {
        $this->route = route('v1.reports.index');
    });

    it('should return an unauthorized response for unauthenticated requests', function () {
        $response = $this->getJson($this->route);

        $response->assertUnauthorized();
    });

    it('should return a bad request if the year parameter is an invalid date', function () {
        $subscriber = User::factory()->create();

        $response = $this->actingAs($subscriber)->getJson("{$this->route}?year=abc");

        $response->assertBadRequest();
        $response->assertSee('The year param must match the format YYYY.');
    });

    it(
        'should return an empty array if the specified subscriber has no associated reports',
        function () {
            $subscriber = User::factory()->create();

            $response = $this->actingAs($subscriber)->getJson($this->route);

            $response->assertOk();
            $response->assertJsonCount(0, 'data');
        }
    );

    it('should return subscriber\'s report list', function () {
        $subscriber = User::factory()->has(Report::factory()->count(12))->create();

        $response = $this->actingAs($subscriber)->getJson($this->route);

        $response->assertOk();
        $response->assertJsonCount(12, 'data');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data', fn (AssertableJson $json) =>
                $json->each(fn (AssertableJson $json) =>
                    $json->where('user', $subscriber->id)->etc()
                )
            )->etc()
        );
    });

    it('should return subscriber\'s report list filtered by year', function () {
        $subscriber = User::factory()->has(Report::factory()->count(12))->create();
        $previousYear = today()->subYear();

        $response = $this->actingAs($subscriber)
            ->getJson("{$this->route}?year={$previousYear->year}");

        $response->assertOk();
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data', fn (AssertableJson $json) =>
                $json->each(fn (AssertableJson $json) =>
                    $json->where('user', $subscriber->id)
                        ->where('period', fn (string $date) => $previousYear->isSameYear($date))
                        ->etc()
                )
            )->etc()
        );
    });

    it('should return the subscriber\'s report list ordered by newest first', function () {
        $this->freezeTime(function() {
            $subscriber = User::factory()
                ->has(Report::factory()
                    ->count(2)
                    ->sequence(
                        ['period' => Carbon::createFromDate(2025, 1, 1)],
                        ['period' => Carbon::createFromDate(2025, 2, 1)],
                    )
                )->create();

            $response = $this->actingAs($subscriber)->getJson("{$this->route}?order=desc");

            $response->assertOk();
            $response->assertJson(fn (AssertableJson $json) =>
                $json->has('data.0', fn(AssertableJson $json) =>
                    $json->where('user', $subscriber->id)
                        ->where('period', Carbon::createFromDate(2025, 2, 1)->toDateTimeString())
                        ->etc()
                )->etc()
            );
        });
    });

    it('should return the subscriber\'s report list ordered by oldest first', function () {
        $this->freezeTime(function() {
            $subscriber = User::factory()
                ->has(Report::factory()
                    ->count(2)
                    ->sequence(
                        ['period' => Carbon::createFromDate(2025, 1, 1)],
                        ['period' => Carbon::createFromDate(2025, 2, 1)],
                    )
                )->create();

            $response = $this->actingAs($subscriber)->getJson("{$this->route}?order=asc");

            $response->assertOk();
            $response->assertJson(fn (AssertableJson $json) =>
                $json->has('data.0', fn(AssertableJson $json) =>
                    $json->where('user', $subscriber->id)
                        ->where('period', Carbon::createFromDate(2025, 1, 1)->toDateTimeString())
                        ->etc()
                )->etc()
            );
        });
    });

    it('should return the correct number of reports per page for the subscriber', function () {
        $subscriber = User::factory()->has(Report::factory()->count(12))->create();

        $response = $this->actingAs($subscriber)->getJson("{$this->route}?perPage=6");

        $response->assertOk();
        $response->assertJsonCount(6, 'data');
    });

    it('should navigate to the next page', function () {
        $subscriber = User::factory()->has(Report::factory()->count(24))->create();

        $response = $this->actingAs($subscriber)->getJson($this->route);

        $nextPage = $response['links']['next'];
        $nextPageResponse = $this->actingAs($subscriber)->getJson($nextPage);

        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('meta', fn (AssertableJson $json) =>
                $json->whereNot('next_cursor', null)->etc()
            )->etc()
        );
        $nextPageResponse->assertJson(fn (AssertableJson $json) =>
            $json->has('meta', fn (AssertableJson $json) =>
                $json->where('next_cursor', null)->etc()
            )->etc()
        );
    });

    it('should navigate to the previous page', function () {
        $subscriber = User::factory()->has(Report::factory()->count(24))->create();

        $response = $this->actingAs($subscriber)->getJson($this->route);

        $nextPage = $response['links']['next'];
        $nextPageResponse = $this->actingAs($subscriber)->getJson($nextPage);

        $previousPage = $nextPageResponse['links']['prev'];
        $previousPageResponse = $this->actingAs($subscriber)->getJson($previousPage);

        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('meta', fn (AssertableJson $json) =>
                $json->where('prev_cursor', null)->etc()
            )->etc()
        );
        $nextPageResponse->assertJson(fn (AssertableJson $json) =>
            $json->has('meta', fn (AssertableJson $json) =>
                $json->whereNot('prev_cursor', null)->etc()
            )->etc()
        );
        $previousPageResponse->assertJson(fn (AssertableJson $json) =>
            $json->has('meta', fn (AssertableJson $json) =>
                $json->where('prev_cursor', null)->etc()
            )->etc()
        );
    });
});
