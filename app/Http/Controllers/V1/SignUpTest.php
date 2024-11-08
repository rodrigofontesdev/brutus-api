<?php

use App\Mail\NewlyRegisteredSubscriber;
use App\Models\Subscriber;
use Illuminate\Testing\Fluent\AssertableJson;

describe('Sign Up', function () {
    beforeEach(function () {
        $this->endpoint = route('v1.sign-up');
        $this->subscriber = [
            'cnpj' => '45536395000180',
            'full_name' => 'John Doe',
            'mobile_phone' => '11990908888',
            'email' => 'doe@example.com',
        ];
    });

    it('should return a bad request if missing a required field', function () {
        $response = $this->postJson($this->endpoint, []);

        $response->assertStatus(400);
        $response->assertInvalid(['cnpj', 'full_name', 'mobile_phone', 'email']);
    });

    it('should return an error if the CNPJ is invalid', function (string $cnpj) {
        $subscriber = [...$this->subscriber, 'cnpj' => $cnpj];

        $response = $this->postJson($this->endpoint, $subscriber);

        $response->assertStatus(400);
        $response->assertInvalid(['cnpj' => 'The cnpj field has an invalid format.']);
    })->with(['123', '123456780001415', '10.123.456/0001-99']);

    it('should return an error if the CNPJ is being used by another subscriber', function () {
        Subscriber::factory()->state(['cnpj' => $this->subscriber['cnpj']])->create();
        $subscriber = $this->subscriber;

        $response = $this->postJson($this->endpoint, $subscriber);

        $response->assertStatus(400);
        $response->assertInvalid([
            'cnpj' => 'The cnpj field is being used by another subscriber.',
        ]);
    });

    it('should return an error if the full name is greather than 100 characteres', function () {
        $subscriber = [...$this->subscriber, 'full_name' => str_repeat('John Doe', 50)];

        $response = $this->postJson($this->endpoint, $subscriber);

        $response->assertStatus(400);
        $response->assertInvalid([
            'full_name' => 'The full name field must not be greater than 100 characters.',
        ]);
    });

    it('should return an error if the mobile phone is invalid', function (string $mobilePhone) {
        $subscriber = [...$this->subscriber, 'mobile_phone' => $mobilePhone];

        $response = $this->postJson($this->endpoint, $subscriber);

        $response->assertStatus(400);
        $response->assertInvalid([
            'mobile_phone' => 'The mobile phone field has an invalid format.',
        ]);
    })->with(['1198765432', '1198765432100', '11 99800-1234']);

    it('should return an error if the email is invalid', function () {
        $subscriber = [...$this->subscriber, 'email' => 'invalid@mail'];

        $response = $this->postJson($this->endpoint, $subscriber);

        $response->assertStatus(400);
        $response->assertInvalid(['email' => 'The email field must be a valid email address.']);
    });

    it('should return an error if the email is being used by another subscriber', function () {
        Subscriber::factory()->state(['email' => $this->subscriber['email']])->create();
        $subscriber = $this->subscriber;

        $response = $this->postJson($this->endpoint, $subscriber);

        $response->assertStatus(400);
        $response->assertInvalid(['email' => 'The email has already been taken.']);
    });

    it('should return an error if the email is greather than 100 characteres', function () {
        $subscriber = [
            ...$this->subscriber,
            'email' => mb_str_pad('doe@example.com', 150, 'doe', STR_PAD_LEFT),
        ];

        $response = $this->postJson($this->endpoint, $subscriber);

        $response->assertStatus(400);
        $response->assertInvalid([
            'email' => 'The email field must not be greater than 100 characters.',
        ]);
    });

    it(
        'should create the subscriber and magic link into the database when the fields are valid',
        function () {
            $subscriber = $this->subscriber;

            $response = $this->postJson($this->endpoint, $subscriber);

            $this->assertDatabaseHas('users', [
                'id' => $response['id'],
                'role' => 'subscriber',
            ]);
            $this->assertDatabaseHas('magic_links', $response['latest_magic_link']);
        }
    );

    it(
        'should send an email to the subscriber with the magic link when the fields are valid',
        function () {
            $subscriber = $this->subscriber;

            $response = $this->postJson($this->endpoint, $subscriber);

            $token = $response['latest_magic_link']['token'];
            $mail = new NewlyRegisteredSubscriber(link: $token);

            $mail->assertSeeInHtml($token);
        }
    );

    it('should return a successful response if the subscriber is created', function () {
        $subscriber = $this->subscriber;

        $response = $this->postJson($this->endpoint, $subscriber);

        $response->assertStatus(201);
        $response->assertJson(
            fn (AssertableJson $json) => $json->where('cnpj', $subscriber['cnpj'])
                ->where('full_name', $subscriber['full_name'])
                ->where('mobile_phone', $subscriber['mobile_phone'])
                ->where('email', $subscriber['email'])
                ->where('role', 'subscriber')
                ->etc()
        );
    });
});
