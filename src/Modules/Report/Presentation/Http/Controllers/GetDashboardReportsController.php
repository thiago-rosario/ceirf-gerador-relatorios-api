<?php

declare(strict_types=1);

namespace src\Modules\Report\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use src\Modules\Report\Application\Interfaces\Usecase\GetDashboardReportsUsecaseInterface;
use src\Modules\Report\Presentation\Resources\ReportResource;
use src\Modules\Shared\Helper\ResponseJsend;

use Throwable;

class GetDashboardReportsController extends Controller
{
    public function __construct(private readonly GetDashboardReportsUsecaseInterface $usecase) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $result = $this->usecase->__invoke();
            return (new ResponseJsend([
                'total_reports' => $result->totalReports,
                'reports' => ReportResource::collection($result->reports)->resolve($request),
            ]))->toJsonResponse();
        } catch (Throwable $e) {
            report($e);

            $response = new ResponseJsend(
                status: ResponseJsend::STATUS_ERROR,
                message: 'An unexpected error occurred',
                code: 500,
            );

            return $response->toJsonResponse(500);
        }
    }
}
