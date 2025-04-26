<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\ApiErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CreateMeiCategoryRequest;
use App\Http\Resources\V1\MeiCategoryResource;
use App\Models\MeiCategory;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ValidatedInput;

class CreateMeiCategory extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to create a new MEI category for the subscriber.');
    }

    public function __invoke(CreateMeiCategoryRequest $request): JsonResponse
    {
        $category = $this->createMeiCategoryInDatabase($request->safe());

        Log::info(
            self::class.':: Finishing to create a new MEI category for the subscriber.',
            ['mei-category' => $category->toArray()]
        );

        return Response::json(new MeiCategoryResource($category), JsonResponse::HTTP_CREATED);
    }

    /**
     * @throws App\Exceptions\V1\ApiErrorException
     */
    private function createMeiCategoryInDatabase(ValidatedInput $data): MeiCategory
    {
        try {
            $category = new MeiCategory();
            $category->user = Auth::id();
            $category->type = $data->type;
            $category->creation_date = Carbon::parse($data->creation_date)->format('Y-m-d');

            if ($data->filled('table_a_excluded_after_032022')) {
                $category->table_a_excluded_after_032022 = $data->table_a_excluded_after_032022;
            }

            $category->save();

            Log::info(
                self::class.':: Subscriber\'s Mei category has been created in the database.',
                ['mei-category' => $category->toArray()]
            );

            return $category;
        } catch (QueryException $error) {
            throw new ApiErrorException(message: self::class.':: Failed to create new MEI category in the database.', previous: $error);
        }
    }
}
