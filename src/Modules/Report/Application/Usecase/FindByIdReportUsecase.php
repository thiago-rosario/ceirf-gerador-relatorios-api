<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\FindByIdReportInputDTO;
use src\Modules\Report\Application\DTO\FindByIdReportOutputDTO;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Application\Interfaces\Service\ReportFinderServiceInterface;
use src\Modules\Report\Application\Interfaces\Usecase\FindByIdReportUsecaseInterface;

class FindByIdReportUsecase implements FindByIdReportUsecaseInterface
{
    public function __construct(
        private readonly ReportFinderServiceInterface $finder,
        private readonly ReportDataMapperServiceInterface $mapper,
    ) {}

    public function __invoke(FindByIdReportInputDTO $input): FindByIdReportOutputDTO
    {
        $report = $this->finder->findById($input->id);

        return new FindByIdReportOutputDTO(report: $this->mapper->map($report));
    }
}
