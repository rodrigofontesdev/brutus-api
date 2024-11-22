<?php

namespace App\Exceptions\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class InvalidCredentialException extends \Exception
{
    public function report(): void
    {
        Log::error($this->message);
    }

    public function render(Request $request): JsonResponse
    {
        return Response::json([
            'type' => 'AUTHENTICATION_ERROR',
            'message' => 'The magic link is expired or already been used.',
            'path' => $request->fullUrl(),
            'started_at' => now()->toDateTimeString(),
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
