<?php

namespace App\Exceptions\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class AuthenticationException extends \Exception
{
    public function __construct(private string $subject)
    {
    }

    public function report(): void
    {
        Log::warning(
            $this->subject,
            [
                'context_id' => Str::ulid()->toString(),
                'request' => [
                    'path' => request()->path(),
                    'method' => request()->method(),
                    'body' => request()->all(),
                ],
            ]
        );
    }

    public function render(Request $request): JsonResponse
    {
        return Response::json([
            'type' => 'AUTHENTICATION_ERROR',
            'message' => 'You need to be authenticated to access the route. Please login to continue.',
            'path' => $request->fullUrl(),
            'started_at' => now()->toDateTimeString(),
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
