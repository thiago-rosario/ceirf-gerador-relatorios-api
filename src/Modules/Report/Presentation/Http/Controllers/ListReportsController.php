<?php

declare(strict_types=1);

namespace src\Modules\Report\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use src\Modules\Report\Application\DTO\FindReportsByCoordinateIdInputDTO;
use src\Modules\Report\Application\DTO\FindReportsByMunicipalityIdInputDTO;
use src\Modules\Report\Application\DTO\FindReportsByUserIdAndCoordinateIdInputDTO;
use src\Modules\Report\Application\DTO\FindReportsByUserIdInputDTO;
use src\Modules\Report\Application\Interfaces\Usecase\FindReportsByCoordinateIdUsecaseInterface;
use src\Modules\Report\Application\Interfaces\Usecase\FindReportsByMunicipalityIdUsecaseInterface;
use src\Modules\Report\Application\Interfaces\Usecase\FindReportsByUserIdAndCoordinateIdUsecaseInterface;
use src\Modules\Report\Application\Interfaces\Usecase\FindReportsByUserIdUsecaseInterface;
use src\Modules\Report\Application\Interfaces\Usecase\GetDashboardReportsUsecaseInterface;
use src\Modules\Report\Presentation\Http\Requests\ListReportsRequest;
use src\Modules\Report\Presentation\Resources\ReportResource;
use src\Modules\Shared\Helper\ResponseJsend;

use Throwable;

class ListReportsController extends Controller
{
    public function __construct(
        private readonly FindReportsByUserIdUsecaseInterface $byUser,
        private readonly FindReportsByCoordinateIdUsecaseInterface $byCoordination,
        private readonly FindReportsByUserIdAndCoordinateIdUsecaseInterface $byUserAndCoordination,
        private readonly FindReportsByMunicipalityIdUsecaseInterface $byMunicipality,
        private readonly GetDashboardReportsUsecaseInterface $dashboard,
    ) {}

    public function __invoke(ListReportsRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = match (true) {
                isset($data['user_id'], $data['coordination_id']) => $this->byUserAndCoordination->__invoke(new FindReportsByUserIdAndCoordinateIdInputDTO($data['user_id'], (string) $data['coordination_id'])),
                isset($data['user_id']) => $this->byUser->__invoke(new FindReportsByUserIdInputDTO($data['user_id'])),
                isset($data['coordination_id']) => $this->byCoordination->__invoke(new FindReportsByCoordinateIdInputDTO((string) $data['coordination_id'])),
                isset($data['municipality_id']) => $this->byMunicipality->__invoke(new FindReportsByMunicipalityIdInputDTO((string) $data['municipality_id'])),
                default => $this->dashboard->__invoke(),
            };
            return (new ResponseJsend(['reports' => ReportResource::collection($result->reports)->resolve($request)]))->toJsonResponse();
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
