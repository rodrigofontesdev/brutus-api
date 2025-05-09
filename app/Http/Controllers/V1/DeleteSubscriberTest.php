<?php

use App\Models\User;
use Illuminate\Support\Str;

describe('Delete Subscriber', function () {
    beforeEach(function () {
        $this->subscriber = User::factory()->create();
    });

    it('should return an unauthorized response for unauthenticated requests', function () {
        $subscriberId = ['id' => Str::uuid()->toString()];

        $response = $this->deleteJson(route('v1.subscribers.delete', $subscriberId));

        $response->assertUnauthorized();
    });

    it('should return a bad request if subscriber ID is an invalid UUID', function () {
        $subscriberId = ['id' => 'invalid'];

        $response = $this->actingAs($this->subscriber)
            ->deleteJson(route('v1.subscribers.delete', $subscriberId));

        $response->assertBadRequest();
        $response->assertSee('The specified subscriber ID in URL is invalid.');
    });

    it('should return a not found response for non-existent subscribers', function () {
        $subscriberId = ['id' => Str::uuid()->toString()];

        $response = $this->actingAs($this->subscriber)
            ->deleteJson(route('v1.subscribers.delete', $subscriberId));

        $response->assertNotFound();
    });

    it(
        'should return a forbidden response if the user attempts to delete another subscriber\'s profile',
        function () {
            $anotherSubscriber = User::factory()->create();
            $subscriberId = ['id' => $anotherSubscriber->id];

            $response = $this->actingAs($this->subscriber)
                ->deleteJson(route('v1.subscribers.delete', $subscriberId));

            $response->assertForbidden();
        }
    );

    it('should delete subscriber from the database', function () {
        $subscriberId = ['id' => $this->subscriber->id];

        $response = $this->actingAs($this->subscriber)
            ->deleteJson(route('v1.subscribers.delete', $subscriberId));

        $response->assertNoContent();
        $this->assertSoftDeleted($this->subscriber);
    });

    it(
        'should log the subscriber out of the application after deleting their own account',
        function () {
            $subscriberId = ['id' => $this->subscriber->id];

            $response = $this->actingAs($this->subscriber)
                ->deleteJson(route('v1.subscribers.delete', $subscriberId));

            $response->assertNoContent();
            $this->assertGuest('web');
        }
    );
});
