<?php

use App\Mail\AuthenticateWithMagickLink;
use App\Models\Subscriber;

describe('Sign In', function () {
    beforeEach(function () {
        $this->route = route('v1.sign-in');
    });

    it('should return a bad request if missing required fields', function () {
        $response = $this->postJson($this->route, []);

        $response->assertBadRequest();
        $response->assertSee([
            'The cnpj field is required.',
        ]);
    });

    it('should return a bad request if CNPJ is invalid', function (string $cnpj) {
        $credential = ['cnpj' => $cnpj];

        $response = $this->postJson($this->route, $credential);

        $response->assertBadRequest();
        $response->assertSee('The cnpj field has an invalid format.');
    })->with(['123', '123456780001415', '10.123.456/0001-99']);

    it('should return a bad request if no user matches CNPJ', function () {
        $credential = ['cnpj' => '00000000000099'];

        $response = $this->postJson($this->route, $credential);

        $response->assertBadRequest();
        $response->assertSee('The cnpj provided does not matches to any user.');
    });

    it('should limit the maximum number of requests per hour', function () {
        $maxLimit = 5;

        while ($maxLimit > 0) {
            $this->postJson($this->route, []);
            --$maxLimit;
        }

        $response = $this->postJson($this->route, []);

        $response->assertTooManyRequests();
    }
    );

    it('should send an email with magic link to subscriber if fields are valid', function () {
        $validSubscriber = Subscriber::factory()->create();
        $credential = ['cnpj' => $validSubscriber->cnpj];

        $response = $this->postJson($this->route, $credential);

        $subscriber = Subscriber::find($validSubscriber->id);
        $mail = new AuthenticateWithMagickLink($subscriber);

        $response->assertNoContent();
        $mail->assertSeeInHtml($subscriber->latestMagicLink->fullUrl());
    });

    it('should include the subscriber\'s secret word in the email', function () {
        $validSubscriber = Subscriber::factory()->create();
        $credential = ['cnpj' => $validSubscriber->cnpj];

        $response = $this->postJson($this->route, $credential);

        $subscriber = Subscriber::find($validSubscriber->id);
        $mail = new AuthenticateWithMagickLink($subscriber);

        $response->assertNoContent();
        $mail->assertSeeInHtml($subscriber->secret_word);
    });
});
