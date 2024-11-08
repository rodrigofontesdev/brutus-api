<?php

use App\Models\MagicLink;
use App\Models\Subscriber;
use Illuminate\Support\Str;

describe('Authenticate', function () {
    beforeEach(function () {
        $this->endpoint = route('v1.authenticate');
        $this->redirectTo = 'https://example.com';
    });

    it('should return a bad request when token is missing', function () {
        $response = $this->postJson($this->endpoint, []);

        $response->assertStatus(400);
        $response->assertInvalid(['token' => 'The token field is required.']);
    });

    it('should return a bad request when redirect is not a valid URL', function () {
        $redirectTo = ['redirect' => 'http://unsecure.com'];

        $response = $this->postJson($this->endpoint, $redirectTo);

        $response->assertStatus(400);
        $response->assertInvalid(['redirect' => 'The redirect field must be a valid URL.']);
    });

    it('should return a bad request when token is not a valid UUID', function () {
        $token = ['token' => 'wrong'];

        $response = $this->postJson($this->endpoint, $token);

        $response->assertStatus(400);
        $response->assertInvalid(['token' => 'The token field must be a valid UUID.']);
    });

    it('should return a bad request when token does not match any record', function () {
        $token = ['token' => Str::uuid()->toString()];

        $response = $this->postJson($this->endpoint, $token);

        $response->assertStatus(400);
        $response->assertInvalid(['token' => 'The selected token is invalid.']);
    });

    it('should return a bad request when the token already been used', function () {
        $subscriber = Subscriber::factory()->has(MagicLink::factory()->used())->create();
        $token = ['token' => $subscriber->latestMagicLink->token];

        $response = $this->postJson($this->endpoint, $token);

        $response->assertStatus(400);
        $response->assertInvalid(['token' => 'The selected token has already been used.']);
    });

    it('should return a bad request if token is expired', function () {
        $subscriber = Subscriber::factory()->has(MagicLink::factory()->expired())->create();
        $token = ['token' => $subscriber->latestMagicLink->token];

        $response = $this->postJson($this->endpoint, $token);

        $response->assertStatus(400);
        $response->assertInvalid(['token' => 'The selected token is expired.']);
    });

    it('should authenticate the user if the token is valid', function () {
        $subscriber = Subscriber::factory()->has(MagicLink::factory())->create();
        $payload = [
            'token' => $subscriber->latestMagicLink->token,
            'redirect' => $this->redirectTo,
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(200);
        $this->assertAuthenticated('web');
    });

    it('should mark magic link as used if the user authenticates successfully', function () {
        $subscriber = Subscriber::factory()->has(MagicLink::factory())->create();
        $payload = [
            'token' => $subscriber->latestMagicLink->token,
            'redirect' => $this->redirectTo,
        ];

        $this->postJson($this->endpoint, $payload);

        $this->assertAuthenticated('web');
        $this->assertDatabaseHas('magic_links', [
            'token' => $payload['token'],
            'used_at' => now()->toDateTimeString(),
        ]);
    });
});
