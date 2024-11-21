<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\ApiErrorException;
use App\Helpers\Generator;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SignUpRequest;
use App\Http\Resources\V1\SubscriberResource;
use App\Mail\NewlyRegisteredSubscriber;
use App\Models\MagicLink;
use App\Models\Subscriber;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Support\ValidatedInput;
use Symfony\Component\Mailer\Exception\TransportException;

class SignUp extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to create a new subscriber.');
    }

    public function __invoke(SignUpRequest $request): JsonResponse
    {
        $validated = $request->safe();

        $subscriber = $this->createSubscriberInDatabase($validated);

        $magicLink = $this->createMagicLinkInDatabase($subscriber);

        $this->sendEmailToSubscriber($subscriber, $magicLink);

        Log::info(self::class.':: Finishing to create a new subscriber.');

        return Response::json(new SubscriberResource($subscriber), JsonResponse::HTTP_CREATED);
    }

    /**
     * @throws ApiErrorException
     */
    private function createSubscriberInDatabase(ValidatedInput $data): Subscriber
    {
        try {
            $subscriber = new Subscriber();
            $subscriber->email = $data->email;
            $subscriber->full_name = $data->full_name;
            $subscriber->cnpj = $data->cnpj;
            $subscriber->mobile_phone = $data->mobile_phone;
            $subscriber->save();

            Log::info(
                self::class.':: Subscriber has been created in the database.',
                ['subscriber' => $subscriber->toArray()]
            );

            return $subscriber;
        } catch (QueryException $error) {
            throw new ApiErrorException(self::class.':: Failed to create new subscriber in the database.', $error);
        }
    }

    /**
     * @throws ApiErrorException
     */
    private function createMagicLinkInDatabase(Subscriber $subscriber): MagicLink
    {
        try {
            $magicLink = new MagicLink();
            $magicLink->token = Str::uuid()->toString();
            $magicLink->user = $subscriber->id;
            $magicLink->expires_at = Generator::magicLinkExpireTime();
            $magicLink->save();

            Log::info(
                self::class.':: Subscriber\'s magic link has been created in the database.',
                ['magic_link' => $magicLink->toArray()]
            );

            return $magicLink;
        } catch (QueryException $error) {
            throw new ApiErrorException(self::class.':: Failed to create subscriber\'s magic link in the database.', $error);
        }
    }

    private function sendEmailToSubscriber(
        Subscriber $subscriber,
        MagicLink $magicLink,
    ): void {
        try {
            Mail::to($subscriber)
                ->send(new NewlyRegisteredSubscriber($magicLink));

            Log::info(self::class.':: A welcome email has been sent to the subscriber\'s email address.');
        } catch (TransportException $error) {
            Log::error(
                self::class.':: Failed to send a welcome email to the subscriber\'s email address.',
                [
                    'exception' => [
                        'origin' => get_class($error),
                        'message' => $error->getMessage(),
                        'stacktrace' => $error->getTraceAsString(),
                    ],
                ]
            );
        }
    }
}
