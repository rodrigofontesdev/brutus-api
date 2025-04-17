<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\AuthorizationException;
use App\Exceptions\V1\InvalidRequestException;
use App\Exceptions\V1\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\MeiCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class DeleteMeiCategory extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to delete subscriber\'s MEI category.');
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
            message: self::class.':: Unable to delete subscriber\'s MEI category due to invalid parameter in URL.',
            validator: ['The specified MEI category ID in URL is invalid.']
        );

        $category = MeiCategory::find($id);

        throw_unless(
            $category,
            NotFoundException::class,
            message: self::class.':: MEI category could not be found.'
        );

        throw_unless(
            $request->user()->can('delete', $category),
            AuthorizationException::class,
            message: self::class.':: User don\'t have sufficient permissions to delete the requested MEI category.'
        );

        $category->delete();

        Log::info(
            self::class.':: Finishing to delete subscriber\'s MEI category.',
            ['mei-category' => $category->toArray()]
        );

        return Response::json(status: JsonResponse::HTTP_NO_CONTENT);
    }
}
