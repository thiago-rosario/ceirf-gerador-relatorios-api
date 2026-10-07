<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\FindReportsByUserIdInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;
use src\Modules\Report\Application\Interfaces\Adapter\ReportQueryResultAdapterInterface;
use src\Modules\Report\Application\Interfaces\Usecase\FindReportsByUserIdUsecaseInterface;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

class FindReportsByUserIdUsecase implements FindReportsByUserIdUsecaseInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
        private readonly ReportQueryResultAdapterInterface $adapter,
    ) {}

    public function __invoke(FindReportsByUserIdInputDTO $input): ListReportsOutputDTO
    {
        $reports = $this->repository->getReportByUserId($input->userId);

        return new ListReportsOutputDTO(
            reports: $reports === null ? [] : $this->adapter->toReports($reports),
        );
    }
}
