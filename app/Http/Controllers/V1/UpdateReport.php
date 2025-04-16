<?php

namespace App\Http\Controllers\V1;

use App\Events\AnnualRevenueChanged;
use App\Exceptions\V1\ApiErrorException;
use App\Exceptions\V1\AuthorizationException;
use App\Exceptions\V1\InvalidRequestException;
use App\Exceptions\V1\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateReportRequest;
use App\Http\Resources\V1\ReportResource;
use App\Models\Report;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Support\ValidatedInput;

class UpdateReport extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to update report.');
    }

    /**
     * @throws App\Exceptions\V1\InvalidRequestException
     * @throws App\Exceptions\V1\NotFoundException
     * @throws App\Exceptions\V1\AuthorizationException
     */
    public function __invoke(UpdateReportRequest $request, string $id): JsonResponse
    {
        throw_unless(
            Str::isUuid($id),
            InvalidRequestException::class,
            message: self::class.':: Unable to update report due to invalid parameter in URL.',
            validator: ['The specified report ID in URL is invalid.']
        );

        $report = Report::find($id);

        throw_unless(
            $report,
            NotFoundException::class,
            message: self::class.':: Report could not be found.'
        );

        throw_unless(
            $request->user()->can('update', $report),
            AuthorizationException::class,
            message: self::class.':: User don\'t have sufficient permissions to update the specified report.'
        );

        $updatedReport = $this->updateReportInDatabase($report, $request->safe());

        AnnualRevenueChanged::dispatch();

        Log::info(
            self::class.':: Finishing to update report.',
            ['report' => $report->toArray()]
        );

        return Response::json(new ReportResource($updatedReport), JsonResponse::HTTP_OK);
    }

    /**
     * @throws App\Exceptions\V1\ApiErrorException
     */
    private function updateReportInDatabase(Report $report, ValidatedInput $data): Report
    {
        try {
            $report->trade_with_invoice = (int) $data->trade_with_invoice;
            $report->trade_without_invoice = (int) $data->trade_without_invoice;
            $report->industry_with_invoice = (int) $data->industry_with_invoice;
            $report->industry_without_invoice = (int) $data->industry_without_invoice;
            $report->services_with_invoice = (int) $data->services_with_invoice;
            $report->services_without_invoice = (int) $data->services_without_invoice;
            $report->save();

            Log::info(
                self::class.':: Report has been updated in the database.',
                ['report' => $report->toArray()]
            );

            return $report;
        } catch (QueryException $error) {
            throw new ApiErrorException(message: self::class.':: Failed to update report in the database.', previous: $error);
        }
    }
}
