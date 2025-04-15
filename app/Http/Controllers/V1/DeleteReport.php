<?php

namespace App\Http\Controllers\V1;

use App\Events\AnnualRevenueChanged;
use App\Exceptions\V1\AuthorizationException;
use App\Exceptions\V1\InvalidRequestException;
use App\Exceptions\V1\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class DeleteReport extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to delete report.');
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
            message: self::class.':: Unable to delete report due to invalid parameter in URL.',
            validator: ['The specified report ID in URL is invalid.']
        );

        $report = Report::find($id);

        throw_unless(
            $report,
            NotFoundException::class,
            message: self::class.':: Report could not be found.'
        );

        throw_unless(
            $request->user()->can('delete', $report),
            AuthorizationException::class,
            message: self::class.':: User don\'t have sufficient permissions to delete the requested report.'
        );

        $report->delete();

        AnnualRevenueChanged::dispatch();

        Log::info(
            self::class.':: Finishing to delete report.',
            ['report' => $report->toArray()]
        );

        return Response::json(status: JsonResponse::HTTP_NO_CONTENT);
    }
}
