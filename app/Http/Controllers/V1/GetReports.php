<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\ApiErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\GetReportsRequest;
use App\Http\Resources\V1\ReportCollection;
use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GetReports extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to fetch subscriber reports.');
    }

    public function __invoke(GetReportsRequest $request): JsonResponse
    {
        try {
            $year = $request->query('year');

            $reports = Report::where('user', $request->user()->id)
                ->when($year, function (Builder $query, string $year) {
                    $query->whereYear('period', $year);
                })
                ->orderByDesc('period')
                ->cursorPaginate(12);

            Log::info(
                self::class.':: Finishing to fetch subscriber reports.',
                ['reports' => $reports->toArray()]
            );

            return (new ReportCollection($reports))
                ->response()
                ->setStatusCode(JsonResponse::HTTP_OK);
        } catch (QueryException $error) {
            throw new ApiErrorException(message: self::class.':: Failed to fetch subscriber reports.', previous: $error);
        }
    }
}
