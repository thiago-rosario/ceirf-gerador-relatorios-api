<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\FindReportHistoryInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Application\Interfaces\Usecase\FindReportHistoryUsecaseInterface;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

class FindReportHistoryUsecase implements FindReportHistoryUsecaseInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
        private readonly ReportDataMapperServiceInterface $mapper,
    ) {}

    public function __invoke(FindReportHistoryInputDTO $input): ListReportsOutputDTO
    {
        $reports = $this->repository->findByRootReportId($input->rootReportId);
        $reportsData = [];

        foreach ($reports as $report) {
            $reportsData[] = $this->mapper->map($report);
        }

        return new ListReportsOutputDTO(reports: $reportsData);
    }
}
