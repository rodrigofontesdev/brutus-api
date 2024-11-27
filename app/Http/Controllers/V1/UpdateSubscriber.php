<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\ApiErrorException;
use App\Exceptions\V1\AuthorizationException;
use App\Exceptions\V1\InvalidRequestException;
use App\Exceptions\V1\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateSubscriberRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Support\ValidatedInput;

class UpdateSubscriber extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to update subscriber.');
    }

    /**
     * @throws App\Exceptions\V1\InvalidRequestException
     * @throws App\Exceptions\V1\NotFoundException
     * @throws App\Exceptions\V1\AuthorizationException
     */
    public function __invoke(UpdateSubscriberRequest $request, string $id): JsonResponse
    {
        throw_unless(
            Str::isUuid($id),
            InvalidRequestException::class,
            message: self::class.':: Unable to update subscriber due to invalid parameter in URL.',
            validator: ['The specified subscriber ID in URL is invalid.']
        );

        $subscriber = User::subscriber()->find($id);

        throw_unless(
            $subscriber,
            NotFoundException::class,
            message: self::class.':: Subscriber could not be found.'
        );

        throw_unless(
            $request->user()->can('update', $subscriber),
            AuthorizationException::class,
            message: self::class.':: User don\'t have sufficient permissions to update the specified subscriber.'
        );

        $updatedSubscriber = $this->updateSubscriberInDatabase($subscriber, $request->safe());

        Log::info(
            self::class.':: Finishing to update subscriber.',
            ['subscriber' => $updatedSubscriber->toArray()]
        );

        return Response::json(new UserResource($updatedSubscriber), JsonResponse::HTTP_OK);
    }

    /**
     * @throws App\Exceptions\V1\ApiErrorException
     */
    private function updateSubscriberInDatabase(User $subscriber, ValidatedInput $data): User
    {
        try {
            if ($data->filled('email')) {
                $subscriber->email = $data->email;
            }

            if ($data->filled('full_name')) {
                $subscriber->full_name = $data->full_name;
            }

            if ($data->filled('mobile_phone')) {
                $subscriber->mobile_phone = $data->mobile_phone;
            }

            if ($data->filled('city')) {
                $subscriber->city = $data->city;
            }

            if ($data->filled('state')) {
                $subscriber->state = $data->state;
            }

            if ($data->filled('mei')) {
                $subscriber->mei = $data->mei;
            }

            if ($data->filled('secret_word')) {
                $subscriber->secret_word = $data->secret_word;
            }

            $subscriber->save();

            return $subscriber;
        } catch (QueryException $error) {
            throw new ApiErrorException(message: self::class.':: Failed to update subscriber in the database.', previous: $error);
        }
    }
}
