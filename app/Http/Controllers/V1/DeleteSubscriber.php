<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\AuthorizationException;
use App\Exceptions\V1\InvalidRequestException;
use App\Exceptions\V1\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class DeleteSubscriber extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to delete subscriber.');
    }

    /**
     * @throws App\Exceptions\V1\InvalidRequestException
     * @throws App\Exceptions\V1\NotFoundException
     * @throws App\Exceptions\V1\AuthorizationException
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        throw_unless(
            Str::isUuid($id),
            InvalidRequestException::class,
            message: self::class.':: Unable to delete subscriber due to invalid parameter in URL.',
            validator: ['The specified subscriber ID in URL is invalid.']
        );

        $subscriber = User::subscriber()->find($id);

        throw_unless(
            $subscriber,
            NotFoundException::class,
            message: self::class.':: Subscriber could not be found.'
        );

        throw_unless(
            $request->user()->can('delete', $subscriber),
            AuthorizationException::class,
            message: self::class.':: User don\'t have sufficient permissions to delete the requested subscriber.'
        );

        $subscriber->delete();

        Log::info(
            self::class.':: Subscriber has been deleted from the database.',
            ['subscriber' => $subscriber->toArray()]
        );

        $this->killSubscriberSession($request);

        Log::info(
            self::class.':: Finishing to delete subscriber.',
            ['subscriber' => $subscriber->toArray()]
        );

        return Response::json(status: JsonResponse::HTTP_NO_CONTENT);
    }

    private function killSubscriberSession(Request $request): void
    {
        if ($request->user()->isSubscriber()) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
