<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

use DateTimeImmutable;

readonly class GeneratedReportDataDTO
{
    public function __construct(
        public string $storageIdentifier,
        public string $fileName,
        public DateTimeImmutable $generatedAt,
    ) {}
}
