<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class FindReportsByUserIdInputDTO
{
    public function __construct(
        public string $userId,
    ) {}
}
