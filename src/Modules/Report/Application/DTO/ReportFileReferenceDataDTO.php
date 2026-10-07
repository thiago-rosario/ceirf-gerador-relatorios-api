<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class ReportFileReferenceDataDTO
{
    public function __construct(
        public string $storageIdentifier,
        public string $fileName,
        public string $mimeType,
        public int $sizeBytes,
        public string $checksum,
    ) {}
}
