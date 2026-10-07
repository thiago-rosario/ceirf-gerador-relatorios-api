<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class ReportAttachmentDataDTO
{
    public function __construct(
        public string $id,
        public string $type,
        public ReportFileReferenceDataDTO $file,
        public string $description,
    ) {}
}
