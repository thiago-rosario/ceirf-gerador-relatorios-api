<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\GetDashboardReportsOutputDTO;
use src\Modules\Report\Application\Interfaces\Adapter\ReportQueryResultAdapterInterface;
use src\Modules\Report\Application\Interfaces\Usecase\GetDashboardReportsUsecaseInterface;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

class GetDashboardReportsUsecase implements GetDashboardReportsUsecaseInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
        private readonly ReportQueryResultAdapterInterface $adapter,
    ) {}

    public function __invoke(): GetDashboardReportsOutputDTO
    {
        $totalReports = $this->repository->countAllReports();
        $reports = $this->repository->getDashboardReports();

        return new GetDashboardReportsOutputDTO(
            totalReports: $totalReports,
            reports: $reports === null ? [] : $this->adapter->toReports($reports),
        );
    }
}
