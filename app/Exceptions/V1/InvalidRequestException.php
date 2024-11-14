<?php

namespace App\Exceptions\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class InvalidRequestException extends \Exception
{
    public function __construct(private string $subject, private Validator $validator)
    {
    }

    public function report(): void
    {
        Log::error(
            $this->subject,
            ['errors' => $this->validator->errors()->all()]
        );
    }

    public function render(Request $request): JsonResponse
    {
        return Response::json([
            'type' => 'INVALID_REQUEST_ERROR',
            'message' => 'The request was made with missing or invalid parameters.',
            'path' => $request->fullUrl(),
            'started_at' => now()->toDateTimeString(),
            'errors' => $this->validator->errors()->all(),
        ], JsonResponse::HTTP_BAD_REQUEST);
    }
}
