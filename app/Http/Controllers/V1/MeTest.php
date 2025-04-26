<?php

use App\Models\User;

describe('Get Authenticated User', function () {
    beforeEach(function () {
        $this->route = route('v1.me');
        $this->user = User::factory()->create();
    });

    it('should return an unauthorized response for unauthenticated requests', function () {
        $response = $this->getJson($this->route);

        $response->assertUnauthorized();
    });

    it('should return the authenticated user', function () {
        $response = $this->actingAs($this->user)->getJson($this->route);

        $response->assertOk();
        $response->assertJson($this->user->toArray());
        $this->assertAuthenticatedAs($this->user, 'web');
    });
});
