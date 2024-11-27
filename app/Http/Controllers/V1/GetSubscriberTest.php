<?php

use App\Models\User;
use Illuminate\Support\Str;

describe('Get Subscriber', function () {
    beforeEach(function () {
        $this->subscriber = User::factory()->create();
    });

    it('should return an unauthorized response for unauthenticated requests', function () {
        $subscriberId = ['id' => Str::uuid()->toString()];

        $response = $this->getJson(route('v1.subscriber.show', $subscriberId));

        $response->assertUnauthorized();
    });

    it('should return a bad request if subscriber ID is an invalid UUID', function () {
        $subscriberId = ['id' => 'invalid'];

        $response = $this->actingAs($this->subscriber)
            ->getJson(route('v1.subscriber.show', $subscriberId));

        $response->assertBadRequest();
        $response->assertSee('The specified subscriber ID in URL is invalid.');
    });

    it('should return a not found response for non-existent subscribers', function () {
        $subscriberId = ['id' => Str::uuid()->toString()];

        $response = $this->actingAs($this->subscriber)
            ->getJson(route('v1.subscriber.show', $subscriberId));

        $response->assertNotFound();
    });

    it(
        'should return a forbidden response if the user attempts to obtain another subscriber\'s profile',
        function () {
            $anotherSubscriber = User::factory()->create();
            $subscriberId = ['id' => $anotherSubscriber->id];

            $response = $this->actingAs($this->subscriber)
                ->getJson(route('v1.subscriber.show', $subscriberId));

            $response->assertForbidden();
        }
    );

    it('should return the requested subscriber profile', function () {
        $subscriberId = ['id' => $this->subscriber->id];

        $response = $this->actingAs($this->subscriber)
            ->getJson(route('v1.subscriber.show', $subscriberId));

        $response->assertOk();
        $response->assertJson($this->subscriber->toArray());
    });
});
