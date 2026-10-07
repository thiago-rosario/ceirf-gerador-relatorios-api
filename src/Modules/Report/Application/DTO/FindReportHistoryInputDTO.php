<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class FindReportHistoryInputDTO
{
    public function __construct(
        public string $rootReportId,
    ) {}
}
