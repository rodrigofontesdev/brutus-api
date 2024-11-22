<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\ApiErrorException;
use App\Helpers\Generator;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SignInRequest;
use App\Mail\AuthenticateWithMagickLink;
use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportException;

class SignIn extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to send a new magic link.');
    }

    public function __invoke(SignInRequest $request): JsonResponse
    {
        $validated = $request->safe();

        $subscriber = User::firstWhere('cnpj', $validated->cnpj);

        $this->createMagicLinkInDatabase($subscriber);

        $this->sendMagicLinkByEmail($subscriber);

        Log::info(self::class.':: Finishing to send a new magic link.');

        return Response::json(status: JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @throws ApiErrorException
     */
    private function createMagicLinkInDatabase(User $subscriber): MagicLink
    {
        try {
            $magicLink = new MagicLink();
            $magicLink->token = Str::uuid()->toString();
            $magicLink->expires_at = Generator::magicLinkExpireTime();
            $subscriber->magicLinks()->save($magicLink);

            Log::info(
                self::class.':: New magic link has been created in the database.',
                ['magic_link' => $magicLink->toArray()]
            );

            return $magicLink;
        } catch (QueryException $error) {
            throw new ApiErrorException(self::class.':: Failed to create new magic link in the database.', $error);
        }
    }

    /**
     * @throws ApiErrorException
     */
    private function sendMagicLinkByEmail(User $subscriber): void
    {
        try {
            Mail::to($subscriber->email)->send(new AuthenticateWithMagickLink($subscriber));

            Log::info(self::class.':: New magic link has been sent to the subscriber\'s email address.');
        } catch (TransportException $error) {
            throw new ApiErrorException(self::class.':: Failed to send a new magic link to the subscriber\'s email address.', $error);
        }
    }
}
