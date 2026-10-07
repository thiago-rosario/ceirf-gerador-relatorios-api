<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class FindByIdReportOutputDTO
{
    public function __construct(
        public ReportDataDTO $report,
    ) {}
}
