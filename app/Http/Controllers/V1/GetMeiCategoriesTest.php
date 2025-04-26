<?php

use App\Models\MeiCategory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;

describe('Get MEI Categories', function () {
    beforeEach(function () {
        $this->route = route('v1.mei-categories.index');
    });

    it('should return an unauthorized response for unauthenticated requests', function () {
        $response = $this->getJson($this->route);

        $response->assertUnauthorized();
    });

    it('should return a bad request if the order parameter is invalid', function () {
        $subscriber = User::factory()->create();

        $response = $this->actingAs($subscriber)->getJson("{$this->route}?order=newest");

        $response->assertBadRequest();
        $response->assertSee('The order param must be asc or desc.');
    });

    it('should return a bad request if the per page parameter is invalid', function () {
        $subscriber = User::factory()->create();

        $response = $this->actingAs($subscriber)->getJson("{$this->route}?perPage=abc");

        $response->assertBadRequest();
        $response->assertSee('The per page field must be an integer.');
    });

    it('should return an empty array if the specified subscriber has no associated MEI category',
        function () {
            $subscriber = User::factory()->create();

            $response = $this->actingAs($subscriber)->getJson($this->route);

            $response->assertJsonCount(0, 'data');
        }
    );

    it('should return subscriber\'s MEI category list', function () {
        $subscriber = User::factory()
            ->has(MeiCategory::factory()
                ->count(2)
                ->sequence(
                    ['type' => 'MEI-GERAL', 'creation_date' => Carbon::createFromDate(2020, 6, 1)],
                    ['type' => 'MEI-TAC', 'creation_date' => Carbon::createFromDate(2022, 3, 31)],
                )
            )->create();

        $response = $this->actingAs($subscriber)->getJson($this->route);

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data', fn (AssertableJson $json) =>
                $json->each(fn (AssertableJson $json) =>
                    $json->where('user', $subscriber->id)->etc()
                )
            )->etc()
        );
    });

    it('should return the subscriber\'s MEI category list ordered by newest first', function () {
        $this->freezeTime(function () {
            $subscriber = User::factory()
            ->has(MeiCategory::factory()
                ->count(2)
                ->sequence(
                    ['type' => 'MEI-GERAL', 'creation_date' => Carbon::createFromDate(2020, 6, 1)],
                    ['type' => 'MEI-TAC', 'creation_date' => Carbon::createFromDate(2022, 3, 31)],
                )
            )->create();

            $response = $this->actingAs($subscriber)->getJson("{$this->route}?order=desc");

            $response->assertOk();
            $response->assertJson(fn (AssertableJson $json) =>
                $json->has('data.0', fn (AssertableJson $json) =>
                    $json->where('user', $subscriber->id)
                        ->where('creation_date', Carbon::createFromDate(2022, 3, 31)->toDateTimeString())
                        ->etc()
                )->etc()
            );
        });
    });

    it('should return the subscriber\'s MEI category list ordered by oldest first', function () {
        $this->freezeTime(function () {
            $subscriber = User::factory()
            ->has(MeiCategory::factory()
                ->count(2)
                ->sequence(
                    ['type' => 'MEI-GERAL', 'creation_date' => Carbon::createFromDate(2020, 6, 1)],
                    ['type' => 'MEI-TAC', 'creation_date' => Carbon::createFromDate(2022, 3, 31)],
                )
            )->create();

            $response = $this->actingAs($subscriber)->getJson("{$this->route}?order=asc");

            $response->assertOk();
            $response->assertJson(fn (AssertableJson $json) =>
                $json->has('data.0', fn (AssertableJson $json) =>
                    $json->where('user', $subscriber->id)
                        ->where('creation_date', Carbon::createFromDate(2020, 6, 1)->toDateTimeString())
                        ->etc()
                )->etc()
            );
        });
    });

    it('should return the correct number of MEI categories per page for the subscriber', function () {
        $subscriber = User::factory()->has(MeiCategory::factory()->count(4))->create();

        $response = $this->actingAs($subscriber)->getJson("{$this->route}?perPage=2");

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    });

    it('should navigate to the next page', function () {
        $subscriber = User::factory()->has(MeiCategory::factory()->count(4))->create();

        $response = $this->actingAs($subscriber)->getJson("{$this->route}?perPage=2");

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
        $subscriber = User::factory()->has(MeiCategory::factory()->count(4))->create();

        $response = $this->actingAs($subscriber)->getJson("{$this->route}?perPage=2");

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
