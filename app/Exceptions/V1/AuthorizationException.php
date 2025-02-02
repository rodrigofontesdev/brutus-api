<?php

namespace App\Exceptions\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class AuthorizationException extends \Exception
{
    public function report(): void
    {
        Log::error($this->message);
    }

    public function render(Request $request): JsonResponse
    {
        return Response::json([
            'type' => 'AUTHORIZATION_ERROR',
            'message' => 'Your current permissions do not allow to perform this action.',
            'path' => $request->fullUrl(),
            'started_at' => now()->toDateTimeString(),
        ], JsonResponse::HTTP_FORBIDDEN);
    }
}
