<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\ApiErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\GetMeiCategoriesRequest;
use App\Http\Resources\V1\MeiCategoryCollection;
use App\Models\MeiCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GetMeiCategories extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to fetch subscriber\'s MEI categories.');
    }

    /**
     * @throws \Illuminate\Database\QueryException
     */
    public function __invoke(GetMeiCategoriesRequest $request): JsonResponse
    {
        try {
            $order = $request->query('order') ?? 'desc';
            $perPage = $request->query('perPage') ?? 100;

            $categories = MeiCategory::where('user', $request->user()->id)
                ->orderBy('creation_date', $order)
                ->orderBy('id')
                ->cursorPaginate($perPage);

            Log::info(
                self::class.':: Finishing to fetch subscriber\'s MEI categories.',
                ['mei-categories' => $categories->toArray()]
            );

            return (new MeiCategoryCollection($categories))
                ->response()
                ->setStatusCode(JsonResponse::HTTP_OK);
        } catch (QueryException $error) {
            throw new ApiErrorException(message: self::class.':: Failed to fetch subscriber\'s MEI categories.', previous: $error);
        }
    }
}
