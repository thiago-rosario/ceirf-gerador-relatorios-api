<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

use DateTimeImmutable;

readonly class CreateReportOutputDTO
{
    public function __construct(
        public string $id,
        public string $status,
        public int $revisionNumber,
        public DateTimeImmutable $createdAt,
    ) {}
}
