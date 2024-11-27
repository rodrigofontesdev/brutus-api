<?php

namespace App\Exceptions\V1;

use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;

class MethodNotAllowedException extends \Exception implements ShouldntReport
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
        $requestMethod = $request->method();
        $allowedMethods = Arr::join($this->headers, ', ');

        return Response::json([
            'type' => 'UNSUPPORTED_METHOD_ERROR',
            'message' => "The {$requestMethod} method is not supported for this route. Allowed methods are {$allowedMethods}.",
            'path' => $request->fullUrl(),
            'started_at' => now()->toDateTimeString(),
        ], JsonResponse::HTTP_METHOD_NOT_ALLOWED, $this->headers);
    }
}
