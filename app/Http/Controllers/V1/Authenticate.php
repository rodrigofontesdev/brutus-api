<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\ApiErrorException;
use App\Exceptions\V1\InvalidCredentialException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AuthenticateRequest;
use App\Models\MagicLink;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class Authenticate extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to authenticate subscriber.');
    }

    public function __invoke(AuthenticateRequest $request): JsonResponse
    {
        $validated = $request->safe();

        $magicLink = $this->markMagicLinkAsUsed($validated->token);

        Auth::loginUsingId($magicLink->user);

        $request->session()->regenerate();

        Log::info(
            self::class.':: Finishing to authenticate subscriber.',
            ['subscriber' => $magicLink->owner->toArray()]
        );

        return Response::json([
            'message' => 'Subscriber successfully authenticated.',
            'redirect' => $validated->redirect,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * @throws ApiErrorException
     */
    private function markMagicLinkAsUsed(string $token): MagicLink
    {
        try {
            $magicLink = MagicLink::firstWhere('token', $token);

            $this->canMagicLinkBeUsed($magicLink);

            $magicLink->used_at = now()->toDateTimeString();
            $magicLink->save();

            Log::info(
                self::class.':: Magic link has been marked as used in the database.',
                ['magic_link' => $magicLink->toArray()]
            );

            return $magicLink;
        } catch (QueryException $error) {
            throw new ApiErrorException(message: self::class.':: Failed to mark the magic link as used in the database.', previous: $error);
        }
    }

    /**
     * @throws InvalidCredentialException
     */
    private function canMagicLinkBeUsed(MagicLink $magicLink): void
    {
        $isMagicLinkInvalid = $magicLink->isUsed() || $magicLink->isExpired();

        throw_if(
            $isMagicLinkInvalid,
            InvalidCredentialException::class,
            self::class.':: Subscriber could not be authenticated due to an expired or used magic link.',
        );
    }
}
