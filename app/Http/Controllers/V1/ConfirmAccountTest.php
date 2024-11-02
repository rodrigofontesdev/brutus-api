<?php

use App\Mail\ConfirmAccountCreated;
use App\Models\Subscriber;

describe('Confirm Account', function () {
    beforeEach(function () {
        $this->endpoint = '/api/v1/confirm-account';
    });

    it('should return a bad request if missing a required field', function () {
        $response = $this->postJson($this->endpoint, []);

        $response->assertStatus(400);
        $response->assertInvalid(['email']);
    });

    it('should return an error if the email is invalid', function () {
        $credential = ['email' => 'invalid@mail'];

        $response = $this->postJson($this->endpoint, $credential);

        $response->assertStatus(400);
        $response->assertInvalid(['email' => 'The email field must be a valid email address.']);
    });

    it('should return an error if no user matches the email', function () {
        $credential = ['email' => 'nobody@mail.com'];

        $response = $this->postJson($this->endpoint, $credential);

        $response->assertStatus(400);
        $response->assertInvalid(['email' => 'The email provided does not matches to any user.']);
    });

    it('should send an email with the magic link when an subscriber is not verified', function () {
        $subscriberFactory = Subscriber::factory()->create();
        $credential = ['email' => $subscriberFactory->email];

        $response = $this->postJson($this->endpoint, $credential);

        $subscriber = Subscriber::where('email', $credential['email'])->firstOrFail();
        $magicLink = $subscriber->latestMagicLink->fullUrl();
        $mail = new ConfirmAccountCreated(link: $magicLink);

        $response->assertStatus(204);
        $mail->assertSeeInHtml($magicLink);
    });

    it(
        'should return an error if the endpoint reached the maximum number of requests per hour',
        function () {
            $i = 1;

            while ($i <= 50) {
                $this->postJson($this->endpoint, []);
                ++$i;
            }

            $this->postJson($this->endpoint, [])->assertTooManyRequests();
        }
    );
});
