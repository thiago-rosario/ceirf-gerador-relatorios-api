<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class UpdateReportOutputDTO
{
    public function __construct(public ReportDataDTO $report) {}
}
