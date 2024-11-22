<?php

namespace App\Exceptions\V1;

use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class RateLimitException extends \Exception implements ShouldntReport
{
    /**
     * @param array<string, mixed> $headers
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        private array $headers = [],
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render(Request $request): JsonResponse
    {
        return Response::json([
            'type' => 'RATE_LIMIT_ERROR',
            'message' => 'You have reached the maximum number of requests per hour. Please wait a while to continue.',
            'path' => $request->fullUrl(),
            'started_at' => now()->toDateTimeString(),
        ], JsonResponse::HTTP_TOO_MANY_REQUESTS, $this->headers);
    }
}
