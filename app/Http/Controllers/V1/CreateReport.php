<?php

namespace App\Http\Controllers\V1;

use App\Events\AnnualRevenueChanged;
use App\Exceptions\V1\ApiErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CreateReportRequest;
use App\Http\Resources\V1\ReportResource;
use App\Models\Report;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ValidatedInput;

class CreateReport extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to create a new report.');
    }

    public function __invoke(CreateReportRequest $request): JsonResponse
    {
        $report = $this->createReportInDatabase($request->safe());

        AnnualRevenueChanged::dispatch();

        Log::info(
            self::class.':: Finishing to create a new report.',
            ['report' => $report->toArray()]
        );

        return Response::json(new ReportResource($report), JsonResponse::HTTP_CREATED);
    }

    /**
     * @throws App\Exceptions\V1\ApiErrorException
     */
    private function createReportInDatabase(ValidatedInput $data): Report
    {
        try {
            $report = new Report;
            $report->user = Auth::id();
            $report->trade_with_invoice = (int) $data->trade_with_invoice;
            $report->trade_without_invoice = (int) $data->trade_without_invoice;
            $report->industry_with_invoice = (int) $data->industry_with_invoice;
            $report->industry_without_invoice = (int) $data->industry_without_invoice;
            $report->services_with_invoice = (int) $data->services_with_invoice;
            $report->services_without_invoice = (int) $data->services_without_invoice;
            $report->period = Carbon::parse($data->period)->format('Y-m-d');
            $report->save();

            Log::info(
                self::class.':: Report has been created in the database.',
                ['report' => $report->toArray()]
            );

            return $report;
        } catch (QueryException $error) {
            throw new ApiErrorException(
                message: self::class.':: Failed to create new report in the database.',
                previous: $error
            );
        }
    }
}
