<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class FindByIdReportInputDTO
{
    public function __construct(
        public string $id,
    ) {}
}
