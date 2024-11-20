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
            'message' => 'Your current permissions do not allow access to this resource.',
            'path' => $request->fullUrl(),
            'started_at' => now()->toDateTimeString(),
        ], JsonResponse::HTTP_FORBIDDEN);
    }
}
