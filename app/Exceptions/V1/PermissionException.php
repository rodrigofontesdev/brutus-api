<?php

namespace App\Exceptions\V1;

use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PermissionException extends \Exception implements ShouldntReport
{
    public function render(Request $request): JsonResponse
    {
        return Response::json([
            'type' => 'PERMISSION_ERROR',
            'message' => 'You need to be authenticated to access the route. Please login to continue.',
            'path' => $request->fullUrl(),
            'started_at' => now()->toDateTimeString(),
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
