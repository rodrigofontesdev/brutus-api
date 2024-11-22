<?php

namespace App\Exceptions\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ApiErrorException extends \Exception
{
    public function report(): void
    {
        Log::alert(
            $this->message,
            [
                'exception' => [
                    'message' => $this->getPrevious()->getMessage(),
                    'stacktrace' => $this->getPrevious()->getTraceAsString(),
                ],
            ]
        );
    }

    public function render(Request $request): JsonResponse
    {
        $appName = config('app.name');

        return Response::json([
            'type' => 'API_ERROR',
            'message' => "Something went wrong on {$appName}'s servers, we are experiencing technical difficulties. Please try again later.",
            'path' => $request->fullUrl(),
            'started_at' => now()->toDateTimeString(),
        ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
