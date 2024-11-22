<?php

use App\Models\MagicLink;
use App\Models\User;

describe('Sign Out', function () {
    beforeEach(function () {
        $this->route = route('v1.sign-out');
    });

    it('should return an unauthorized response for unauthenticated users', function () {
        $response = $this->postJson($this->route);

        $response->assertUnauthorized();
    });

    it('should log the user out of the application', function () {
        $subscriber = User::factory()->has(MagicLink::factory()->used())->create();

        $response = $this->actingAs($subscriber)->postJson($this->route);

        $response->assertNoContent();
        $this->assertGuest('web');
    });
});
