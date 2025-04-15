<?php

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;

describe('Update Subscriber', function () {
    beforeEach(function () {
        $this->subscriber = User::factory()->create();
    });

    it('should return an unauthorized response for unauthenticated requests', function () {
        $subscriberId = ['id' => Str::uuid()->toString()];

        $response = $this->patchJson(route('v1.subscribers.update', $subscriberId));

        $response->assertUnauthorized();
    });

    it('should return a bad request if subscriber ID is an invalid UUID', function () {
        $subscriberId = ['id' => 'invalid'];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId));

        $response->assertBadRequest();
        $response->assertSee('The specified subscriber ID in URL is invalid.');
    });

    it('should return a not found response for non-existent subscribers', function () {
        $subscriberId = ['id' => Str::uuid()->toString()];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId));

        $response->assertNotFound();
    });

    it(
        'should return a forbidden response if the user attempts to update another subscriber\'s profile',
        function () {
            $anotherSubscriber = User::factory()->create();
            $subscriberId = ['id' => $anotherSubscriber->id];
            $payload = ['full_name' => 'John Doe'];

            $response = $this->actingAs($this->subscriber)
                ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

            $response->assertForbidden();
        }
    );

    it('should return a bad request if email is invalid', function () {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['email' => 'invalid@mail'];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertBadRequest();
        $response->assertSee('The email field must be a valid email address.');
    });

    it('should return a bad request if email is already been used by another user',
        function () {
            $anotherUser = User::factory()->create();
            $subscriberId = ['id' => $this->subscriber->id];
            $payload = ['email' => $anotherUser->email];

            $response = $this->actingAs($this->subscriber)
                ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

            $response->assertBadRequest();
            $response->assertSee('The email has already been taken.');
        }
    );

    it('should return a bad request if email is greather than 100 characteres', function () {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['email' => mb_str_pad('doe@example.com', 150, 'doe', STR_PAD_LEFT)];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertBadRequest();
        $response->assertSee('The email field must not be greater than 100 characters.');
    });

    it('should return a bad request if full name is greather than 100 characteres', function () {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['full_name' => str_repeat('John Doe', 50)];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertBadRequest();
        $response->assertSee('The full name field must not be greater than 100 characters.');
    });

    it('should return bad request if mobile phone is invalid', function (string $mobilePhone) {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['mobile_phone' => $mobilePhone];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertBadRequest();
        $response->assertSee('The mobile phone field has an invalid format.');
    })->with(['1198765432', '1198765432100', '11 99800-1234']);

    it('should return a bad request if city is greather than 100 characteres', function () {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['city' => str_repeat('São Paulo', 50)];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertBadRequest();
        $response->assertSee('The city field must not be greater than 100 characters.');
    });

    it('should return a bad request if state is invalid', function () {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['state' => 'AB'];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertBadRequest();
        $response->assertSee('The selected state is invalid.');
    });

    it('should return a bad request if secret word is greather than 50 characteres', function () {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['secret_word' => str_repeat('Lorem Ipsum', 10)];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertBadRequest();
        $response->assertSee('The secret word field must not be greater than 50 characters.');
    });

    it('should update subscriber\'s email', function () {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['email' => 'doe@example.com'];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertOk();
        $response->assertJson(
            fn (AssertableJson $json) => $json->where('email', 'doe@example.com')->etc()
        );
        $this->assertDatabaseHas('users', [
            'email' => 'doe@example.com',
        ]);
    });

    it('should update subscriber\'s full name', function () {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['full_name' => 'John Doe'];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertOk();
        $response->assertJson(
            fn (AssertableJson $json) => $json->where('full_name', 'John Doe')->etc()
        );
        $this->assertDatabaseHas('users', [
            'full_name' => 'John Doe',
        ]);
    });

    it('should update subscriber\'s mobile phone', function () {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['mobile_phone' => '11999887766'];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertOk();
        $response->assertJson(
            fn (AssertableJson $json) => $json->where('mobile_phone', '11999887766')->etc()
        );
        $this->assertDatabaseHas('users', [
            'mobile_phone' => '11999887766',
        ]);
    });

    it('should update subscriber\'s city', function () {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['city' => 'Acrelândia'];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertOk();
        $response->assertJson(fn (AssertableJson $json) => $json->where('city', 'Acrelândia')->etc());
        $this->assertDatabaseHas('users', [
            'city' => 'Acrelândia',
        ]);
    });

    it('should update subscriber\'s state', function () {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['state' => 'AC'];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertOk();
        $response->assertJson(fn (AssertableJson $json) => $json->where('state', 'AC')->etc());
        $this->assertDatabaseHas('users', [
            'state' => 'AC',
        ]);
    });

    it('should update subscriber\'s secret word', function () {
        $subscriberId = ['id' => $this->subscriber->id];
        $payload = ['secret_word' => 'super secret'];

        $response = $this->actingAs($this->subscriber)
            ->patchJson(route('v1.subscribers.update', $subscriberId), $payload);

        $response->assertOk();
        $response->assertJson(
            fn (AssertableJson $json) => $json->where('secret_word', Str::upper('super secret'))->etc()
        );
        $this->assertDatabaseHas('users', [
            'secret_word' => 'super secret',
        ]);
    });
});
