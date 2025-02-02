<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\ApiErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateReportRequest;
use App\Http\Resources\V1\ReportResource;
use App\Models\Report;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class UpdateReport extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to update report.');
    }

    /**
     * @throws App\Exceptions\V1\ApiErrorException;
     */
    public function __invoke(UpdateReportRequest $request, Report $report): JsonResponse
    {
        try {
            $report->trade_with_invoice = (int) $request->trade_with_invoice;
            $report->trade_without_invoice = (int) $request->trade_without_invoice;
            $report->industry_with_invoice = (int) $request->industry_with_invoice;
            $report->industry_without_invoice = (int) $request->industry_without_invoice;
            $report->services_with_invoice = (int) $request->services_with_invoice;
            $report->services_without_invoice = (int) $request->services_without_invoice;
            $report->save();

            Log::info(
                self::class.':: Finishing to update report.',
                ['report' => $report->toArray()]
            );

            return Response::json(new ReportResource($report), JsonResponse::HTTP_OK);
        } catch (QueryException $error) {
            throw new ApiErrorException(
                message: self::class.':: Failed to update report in the database.',
                previous: $error
            );
        }
    }
}
