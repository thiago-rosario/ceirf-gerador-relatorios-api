<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class FindLatestReportInputDTO
{
    public function __construct(
        public string $id,
    ) {}
}
