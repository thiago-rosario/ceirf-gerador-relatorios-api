<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class ReportImageDataDTO
{
    public function __construct(
        public string $id,
        public ReportFileReferenceDataDTO $file,
        public int $order,
        public string $caption,
    ) {}
}
