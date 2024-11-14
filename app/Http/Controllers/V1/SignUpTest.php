<?php

use App\Mail\NewlyRegisteredSubscriber;
use App\Models\Subscriber;
use Illuminate\Testing\Fluent\AssertableJson;

describe('Sign Up', function () {
    beforeEach(function () {
        $this->route = route('v1.sign-up');
        $this->subscriber = [
            'cnpj' => '45536395000180',
            'full_name' => 'John Doe',
            'mobile_phone' => '11990908888',
            'email' => 'doe@example.com',
        ];
    });

    it('should return a bad request if missing required fields', function () {
        $response = $this->postJson($this->route, []);

        $response->assertBadRequest();
        $response->assertSee([
            'The cnpj field is required.',
            'The full name field is required.',
            'The mobile phone field is required.',
            'The email field is required.',
        ]);
    });

    it('should return a bad request if CNPJ is invalid', function (string $cnpj) {
        $subscriber = [...$this->subscriber, 'cnpj' => $cnpj];

        $response = $this->postJson($this->route, $subscriber);

        $response->assertBadRequest();
        $response->assertSee('The cnpj field has an invalid format.');
    })->with(['123', '123456780001415', '10.123.456/0001-99']);

    it('should return a bad request if CNPJ is already been used by another subscriber',
        function () {
            Subscriber::factory()->create(['cnpj' => $this->subscriber['cnpj']]);
            $subscriber = $this->subscriber;

            $response = $this->postJson($this->route, $subscriber);

            $response->assertBadRequest();
            $response->assertSee('The cnpj has already been used by another user.');
        }
    );

    it('should return a bad request if full name is greather than 100 characteres', function () {
        $subscriber = [...$this->subscriber, 'full_name' => str_repeat('John Doe', 50)];

        $response = $this->postJson($this->route, $subscriber);

        $response->assertBadRequest();
        $response->assertSee('The full name field must not be greater than 100 characters.');
    });

    it('should return bad request if mobile phone is invalid', function (string $mobilePhone) {
        $subscriber = [...$this->subscriber, 'mobile_phone' => $mobilePhone];

        $response = $this->postJson($this->route, $subscriber);

        $response->assertBadRequest();
        $response->assertSee('The mobile phone field has an invalid format.');
    })->with(['1198765432', '1198765432100', '11 99800-1234']);

    it('should return a bad request if email is invalid', function () {
        $subscriber = [...$this->subscriber, 'email' => 'invalid@mail'];

        $response = $this->postJson($this->route, $subscriber);

        $response->assertBadRequest();
        $response->assertSee('The email field must be a valid email address.');
    });

    it('should return a bad request if email is already been used by another subscriber',
        function () {
            Subscriber::factory()->create(['email' => $this->subscriber['email']]);
            $subscriber = $this->subscriber;

            $response = $this->postJson($this->route, $subscriber);

            $response->assertBadRequest();
            $response->assertSee('The email has already been taken.');
        }
    );

    it('should return a bad request if email is greather than 100 characteres', function () {
        $subscriber = [
            ...$this->subscriber,
            'email' => mb_str_pad('doe@example.com', 150, 'doe', STR_PAD_LEFT),
        ];

        $response = $this->postJson($this->route, $subscriber);

        $response->assertBadRequest();
        $response->assertSee('The email field must not be greater than 100 characters.');
    });

    it('should create the subscriber and a first magic link in database if fields are valid',
        function () {
            $subscriber = $this->subscriber;

            $response = $this->postJson($this->route, $subscriber);

            $subscriberCreated = Subscriber::find($response['id']);
            $magicLink = $subscriberCreated->latestMagicLink;

            $this->assertDatabaseHas('users', ['id' => $response['id'], 'role' => 'subscriber']);
            $this->assertDatabaseHas('magic_links', ['token' => $magicLink->token]);
        }
    );

    it('should send an email with magic link to subscriber if fields are valid', function () {
        $subscriber = $this->subscriber;

        $response = $this->postJson($this->route, $subscriber);

        $subscriberCreated = Subscriber::find($response['id']);
        $magicLink = $subscriberCreated->latestMagicLink;
        $mail = new NewlyRegisteredSubscriber($magicLink);

        $mail->assertSeeInHtml($magicLink->token);
    });

    it('should return a successful response if subscriber is created', function () {
        $subscriber = $this->subscriber;

        $response = $this->postJson($this->route, $subscriber);

        $response->assertCreated();
        $response->assertJson(
            fn (AssertableJson $json) => $json->where('cnpj', $subscriber['cnpj'])
                ->where('full_name', $subscriber['full_name'])
                ->where('mobile_phone', $subscriber['mobile_phone'])
                ->where('email', $subscriber['email'])
                ->etc()
        );
    });
});
