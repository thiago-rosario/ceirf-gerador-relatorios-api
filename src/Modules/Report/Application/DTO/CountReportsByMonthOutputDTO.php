<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class CountReportsByMonthOutputDTO
{
    /**
     * @param  list<MonthlyReportCountDataDTO>  $counts
     */
    public function __construct(
        public string $month,
        public array $counts,
    ) {}
}
