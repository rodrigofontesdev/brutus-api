<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class Me extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return Response::json(new UserResource($request->user()), JsonResponse::HTTP_OK);
    }
}
