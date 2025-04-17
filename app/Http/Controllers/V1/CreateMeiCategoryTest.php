<?php

use App\Models\MeiCategory;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

describe('Create MEI Category', function() {
    beforeEach(function () {
        $this->subscriber = User::factory()->create();
    });

    it('should return an unauthorized response for unauthenticated requests', function () {
        $response = $this->postJson(route('v1.mei-categories.create'), []);

        $response->assertUnauthorized();
    });

    it('should return a bad request if missing required fields', function () {
        $response = $this->actingAs($this->subscriber)
            ->postJson(route('v1.mei-categories.create'), []);

        $response->assertBadRequest();
        $response->assertSee([
            'The type field is required.',
            'The creation date field is required.',
        ]);
    });

    it('should return a bad request if type field has a wrong value', function() {
        $payload = ['type' => 'MEI-NOITE'];

        $response = $this->actingAs($this->subscriber)
            ->postJson(route('v1.mei-categories.create'), $payload);

        $response->assertBadRequest();
        $response->assertSee('The selected type is invalid.');
    });

    it('should return a bad request if the creation date field has an invalid date',
        function (string $date) {
            $payload = ['creation_date' => $date];

            $response = $this->actingAs($this->subscriber)
                ->postJson(route('v1.mei-categories.create'), $payload);

            $response->assertBadRequest();
            $response->assertSee('The creation date field must match the format Y-m-d.');
        }
    )->with(['January 1, 2025', '01-01-2025', '01/01/2025']);

    it('should return a bad request if the "table A excluded after March 31, 2022" field is not boolean',
        function () {
            $payload = ['table_a_excluded_after_032022' => 'abc'];

            $response = $this->actingAs($this->subscriber)
                ->postJson(route('v1.mei-categories.create'), $payload);

            $response->assertBadRequest();
            $response->assertSee('The table A excluded field must be true or false.');
        }
    );

    it('should return a bad request if the user attempts to create a MEI category for a date that already exists',
        function () {
            $category = MeiCategory::factory()->for($this->subscriber, 'owner')
                ->geral()
                ->state(['creation_date' => '2025-01-01'])
                ->create();
            $payload = [
                'type' => 'MEI-GERAL',
                'creation_date' => $category->creation_date
            ];

            $response = $this->actingAs($this->subscriber)
                ->postJson(route('v1.mei-categories.create'), $payload);

            $response->assertBadRequest();
            $response->assertSee('A MEI category for the specified date already exists.');
        }
    );

    it('should create the category in the database if the fields are valid', function () {
        $payload = [
            'type' => 'MEI-GERAL',
            'creation_date' => '2022-01-01'
        ];

        $response = $this->actingAs($this->subscriber)
            ->postJson(route('v1.mei-categories.create'), $payload);

        $categoryCreated = MeiCategory::find($response['id']);

        $this->assertModelExists($categoryCreated);
    });

    it('should return a successful response if the report is created', function () {
        $payload = [
            'type' => 'MEI-GERAL',
            'creation_date' => '2022-01-01'
        ];

        $response = $this->actingAs($this->subscriber)
            ->postJson(route('v1.mei-categories.create'), $payload);

        $response->assertCreated();
        $response->assertJson(
            fn (AssertableJson $json) => $json->where('user', $this->subscriber->id)
                ->where('creation_date', '2022-01-01')
                ->etc()
        );
    });
});
