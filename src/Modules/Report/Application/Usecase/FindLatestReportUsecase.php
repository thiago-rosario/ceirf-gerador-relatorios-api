<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\FindLatestReportInputDTO;
use src\Modules\Report\Application\DTO\FindLatestReportOutputDTO;
use src\Modules\Report\Application\Exception\ReportNotFoundException;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Application\Interfaces\Service\ReportFinderServiceInterface;
use src\Modules\Report\Application\Interfaces\Usecase\FindLatestReportUsecaseInterface;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

class FindLatestReportUsecase implements FindLatestReportUsecaseInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
        private readonly ReportFinderServiceInterface $finder,
        private readonly ReportDataMapperServiceInterface $mapper,
    ) {}

    public function __invoke(FindLatestReportInputDTO $input): FindLatestReportOutputDTO
    {
        $report = $this->finder->findById($input->id);
        $latestReport = $this->repository->findLatestByRootReportId($report->rootReportId()->value());

        if ($latestReport === null) {
            throw new ReportNotFoundException;
        }

        return new FindLatestReportOutputDTO(report: $this->mapper->map($latestReport));
    }
}
