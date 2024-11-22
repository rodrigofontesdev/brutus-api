<?php

namespace App\Exceptions\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class AuthenticationException extends \Exception
{
    public function report(): void
    {
        Log::warning($this->message);
    }

    public function render(Request $request): JsonResponse
    {
        return Response::json([
            'type' => 'AUTHENTICATION_ERROR',
            'message' => 'You need to be authenticated to access this route. Please login to continue.',
            'path' => $request->fullUrl(),
            'started_at' => now()->toDateTimeString(),
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
