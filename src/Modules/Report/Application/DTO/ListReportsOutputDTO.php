<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class ListReportsOutputDTO
{
    /**
     * @param  list<ReportDataDTO>  $reports
     */
    public function __construct(
        public array $reports,
    ) {}
}
