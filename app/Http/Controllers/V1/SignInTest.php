<?php

use App\Mail\AuthenticateWithMagickLink;
use App\Models\Subscriber;

describe('Sign In', function () {
    beforeEach(function () {
        $this->endpoint = route('v1.sign-in');
    });

    it('should return a bad request if missing a required field', function () {
        $response = $this->postJson($this->endpoint, []);

        $response->assertStatus(400);
        $response->assertInvalid(['cnpj']);
    });

    it('should return an error if the CNPJ is invalid', function (string $cnpj) {
        $credential = ['cnpj' => $cnpj];

        $response = $this->postJson($this->endpoint, $credential);

        $response->assertStatus(400);
        $response->assertInvalid(['cnpj' => 'The cnpj field has an invalid format.']);
    })->with(['123', '123456780001415', '10.123.456/0001-99']);

    it('should return an error if no user matches the CNPJ', function () {
        $credential = ['cnpj' => '00000000000099'];

        $response = $this->postJson($this->endpoint, $credential);

        $response->assertStatus(400);
        $response->assertInvalid(['cnpj' => 'The cnpj provided does not matches to any user.']);
    });

    it('should send an email with the magic link when an subscriber is verified', function () {
        $subscriberFactory = Subscriber::factory()->create();
        $credential = ['cnpj' => $subscriberFactory->cnpj];

        $response = $this->postJson($this->endpoint, $credential);

        $subscriber = Subscriber::where('cnpj', $credential['cnpj'])->firstOrFail();
        $magicLink = $subscriber->latestMagicLink->fullUrl();
        $mail = new AuthenticateWithMagickLink(
            link: $magicLink,
            secretWord: $subscriber->secret_word
        );

        $response->assertStatus(204);
        $mail->assertSeeInHtml($subscriber->secret_word);
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
