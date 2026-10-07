<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class GetDashboardReportsOutputDTO
{
    /**
     * @param  list<ReportDataDTO>  $reports
     */
    public function __construct(
        public int $totalReports,
        public array $reports,
    ) {}
}
