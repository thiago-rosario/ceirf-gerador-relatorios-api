<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\CountReportsByMonthInputDTO;
use src\Modules\Report\Application\DTO\CountReportsByMonthOutputDTO;
use src\Modules\Report\Application\Interfaces\Adapter\ReportQueryResultAdapterInterface;
use src\Modules\Report\Application\Interfaces\Usecase\CountReportsByMonthUsecaseInterface;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

class CountReportsByMonthUsecase implements CountReportsByMonthUsecaseInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
        private readonly ReportQueryResultAdapterInterface $adapter,
    ) {}

    public function __invoke(CountReportsByMonthInputDTO $input): CountReportsByMonthOutputDTO
    {
        $counts = $this->repository->countReportsByMonth($input->month);

        return new CountReportsByMonthOutputDTO(
            month: $input->month,
            counts: $counts === null ? [] : $this->adapter->toMonthlyCounts($counts),
        );
    }
}
