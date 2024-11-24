<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\InvalidRequestException;
use App\Exceptions\V1\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class GetSubscriber extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to fetch subscriber profile.');
    }

    public function __invoke(string $id): JsonResponse
    {
        throw_unless(
            Str::isUuid($id),
            InvalidRequestException::class,
            message: self::class.':: Unable to fetch subscriber profile due to invalid parameter.',
            validator: ['The specified subscriber ID is not valid.']
        );

        $subscriber = User::subscriber()->find($id);

        throw_unless(
            $subscriber,
            NotFoundException::class,
            message: self::class.':: Subscriber profile could not be found.'
        );

        Log::info(
            self::class.':: Finishing to obtain the requested subscriber profile.',
            ['subscriber' => $subscriber->toArray()]
        );

        return Response::json(new UserResource($subscriber), JsonResponse::HTTP_OK);
    }
}
