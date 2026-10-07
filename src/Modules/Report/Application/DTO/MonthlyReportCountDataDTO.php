<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class MonthlyReportCountDataDTO
{
    public function __construct(
        public string $month,
        public int $count,
    ) {}
}
