<?php

namespace App\Exceptions\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class NotFoundException extends \Exception
{
    public function report(): void
    {
        Log::error($this->message);
    }

    public function render(Request $request): JsonResponse
    {
        return Response::json([
            'type' => 'NOT_FOUND_ERROR',
            'message' => 'The requested resource could not be found. It may not exist or may be disabled.',
            'path' => $request->fullUrl(),
            'started_at' => now()->toDateTimeString(),
        ], JsonResponse::HTTP_NOT_FOUND);
    }
}
