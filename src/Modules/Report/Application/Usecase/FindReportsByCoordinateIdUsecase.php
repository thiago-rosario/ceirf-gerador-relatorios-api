<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\FindReportsByCoordinateIdInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;
use src\Modules\Report\Application\Interfaces\Adapter\ReportQueryResultAdapterInterface;
use src\Modules\Report\Application\Interfaces\Usecase\FindReportsByCoordinateIdUsecaseInterface;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

class FindReportsByCoordinateIdUsecase implements FindReportsByCoordinateIdUsecaseInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
        private readonly ReportQueryResultAdapterInterface $adapter,
    ) {}

    public function __invoke(FindReportsByCoordinateIdInputDTO $input): ListReportsOutputDTO
    {
        $reports = $this->repository->getReportByCoordinateId($input->coordinateId);

        return new ListReportsOutputDTO(
            reports: $reports === null ? [] : $this->adapter->toReports($reports),
        );
    }
}
