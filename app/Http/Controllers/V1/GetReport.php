<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\V1\AuthorizationException;
use App\Exceptions\V1\InvalidRequestException;
use App\Exceptions\V1\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ReportResource;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class GetReport extends Controller
{
    public function __construct()
    {
        Log::info(self::class.':: Starting to fetch report.');
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
            message: self::class.':: Unable to fetch report due to invalid parameter in URL.',
            validator: ['The specified report ID in URL is invalid.']
        );

        $report = Report::find($id);

        throw_unless(
            $report,
            NotFoundException::class,
            message: self::class.':: Report could not be found.'
        );

        throw_unless(
            $request->user()->can('view', $report),
            AuthorizationException::class,
            message: self::class.':: User don\'t have sufficient permissions to obtain the requested report.'
        );

        Log::info(
            self::class.':: Finishing to obtain the requested report.',
            ['report' => $report->toArray()]
        );

        return Response::json(new ReportResource($report), JsonResponse::HTTP_OK);
    }
}
