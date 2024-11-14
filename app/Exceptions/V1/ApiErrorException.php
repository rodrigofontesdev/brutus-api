<?php

namespace App\Exceptions\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ApiErrorException extends \Exception
{
    public function __construct(private string $subject, private \Exception $origin)
    {
    }

    public function report(): void
    {
        Log::alert(
            $this->subject,
            [
                'exception' => [
                    'origin' => get_class($this->origin),
                    'message' => $this->origin->getMessage(),
                    'stacktrace' => $this->origin->getTraceAsString(),
                ],
            ]
        );
    }

    public function render(Request $request): JsonResponse
    {
        $appName = config('app.name');

        return Response::json([
            'type' => 'API_ERROR',
            'message' => "Something went wrong on {$appName}'s servers.",
            'path' => $request->fullUrl(),
            'started_at' => now()->toDateTimeString(),
            'errors' => [
                'Our servers are experiencing technical difficulties. Please try again later.',
            ],
        ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
}
