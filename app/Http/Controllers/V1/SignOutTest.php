<?php

use App\Models\MagicLink;
use App\Models\Subscriber;

describe('Sign Out', function () {
    beforeEach(function () {
        $this->endpoint = route('sign-out');
    });

    it('should return a permission error if the user is not authenticated', function () {
        $response = $this->postJson($this->endpoint);

        $response->assertStatus(401);
        $response->assertJson(['code' => 'authentication_required']);
    });

    it('should log out the user of the application', function () {
        $subscriber = Subscriber::factory()->has(MagicLink::factory()->used())->create();

        $response = $this->actingAs($subscriber)->postJson($this->endpoint);

        $response->assertStatus(204);
        $this->assertGuest('web');
    });
});
