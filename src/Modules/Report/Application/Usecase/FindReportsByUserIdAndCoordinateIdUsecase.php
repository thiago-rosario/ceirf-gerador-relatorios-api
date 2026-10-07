<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\FindReportsByUserIdAndCoordinateIdInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;
use src\Modules\Report\Application\Interfaces\Adapter\ReportQueryResultAdapterInterface;
use src\Modules\Report\Application\Interfaces\Usecase\FindReportsByUserIdAndCoordinateIdUsecaseInterface;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

class FindReportsByUserIdAndCoordinateIdUsecase implements FindReportsByUserIdAndCoordinateIdUsecaseInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
        private readonly ReportQueryResultAdapterInterface $adapter,
    ) {}

    public function __invoke(FindReportsByUserIdAndCoordinateIdInputDTO $input): ListReportsOutputDTO
    {
        $reports = $this->repository->getReportByUserIdAndCoordinateId($input->userId, $input->coordinateId);

        return new ListReportsOutputDTO(
            reports: $reports === null ? [] : $this->adapter->toReports($reports),
        );
    }
}
