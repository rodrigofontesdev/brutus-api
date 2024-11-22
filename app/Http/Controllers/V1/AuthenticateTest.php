<?php

use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

describe('Authenticate', function () {
    beforeEach(function () {
        $this->route = route('v1.authenticate');
        $this->redirectTo = 'https://example.com';
    });

    it('should return a bad request for missing tokens', function () {
        $response = $this->postJson($this->route, []);

        $response->assertBadRequest();
        $response->assertSee('The token field is required.');
    });

    it('should return a bad request for invalid redirect URL', function () {
        $redirectTo = ['redirect' => 'http://example.com'];

        $response = $this->postJson($this->route, $redirectTo);

        $response->assertBadRequest();
        $response->assertSee('The redirect field must be a valid URL.');
    });

    it('should return a bad request if the token is an invalid UUID', function () {
        $token = ['token' => 'wrong'];

        $response = $this->postJson($this->route, $token);

        $response->assertBadRequest();
        $response->assertSee('The token field must be a valid UUID.');
    });

    it('should return a bad request for non-existent tokens', function () {
        $token = ['token' => Str::uuid()->toString()];

        $response = $this->postJson($this->route, $token);

        $response->assertBadRequest();
        $response->assertSee('The selected token is invalid.');
    });

    it('should return an unauthorized response for reused tokens', function () {
        $subscriber = User::factory()->has(MagicLink::factory()->used())->create();
        $token = ['token' => $subscriber->latestMagicLink->token];

        $response = $this->postJson($this->route, $token);

        $response->assertUnauthorized();
    });

    it('should return an unauthorized response for expired tokens', function () {
        $subscriber = User::factory()->has(MagicLink::factory()->expired())->create();
        $token = ['token' => $subscriber->latestMagicLink->token];

        $response = $this->postJson($this->route, $token);

        $response->assertUnauthorized();
    });

    it('should authenticate the user if the provided token is valid', function () {
        $subscriber = User::factory()->has(MagicLink::factory())->create();
        $payload = [
            'token' => $subscriber->latestMagicLink->token,
            'redirect' => $this->redirectTo,
        ];

        $response = $this->postJson($this->route, $payload);

        $response->assertOk();
        $this->assertAuthenticated('web');
    });

    it('should mark the magic link as used after successful user authentication', function () {
        $subscriber = User::factory()->has(MagicLink::factory())->create();
        $payload = [
            'token' => $subscriber->latestMagicLink->token,
            'redirect' => $this->redirectTo,
        ];

        $this->postJson($this->route, $payload);

        $this->assertAuthenticated('web');
        $this->assertDatabaseHas('magic_links', [
            'token' => $payload['token'],
            'used_at' => now()->toDateTimeString(),
        ]);
    });

    it('should expire user session after 24 hours', function () {
        $subscriber = User::factory()->has(MagicLink::factory())->create();
        $payload = [
            'token' => $subscriber->latestMagicLink->token,
            'redirect' => $this->redirectTo,
        ];

        $response = $this->postJson($this->route, $payload);

        $forwardOneDayAhead = now()->addDay();

        Carbon::setTestNow($forwardOneDayAhead);
        $response->assertCookieExpired(config('session.cookie'));
        Carbon::setTestNow();
    });
});
