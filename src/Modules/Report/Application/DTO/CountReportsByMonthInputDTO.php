<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class CountReportsByMonthInputDTO
{
    public function __construct(
        public string $month,
    ) {}
}
