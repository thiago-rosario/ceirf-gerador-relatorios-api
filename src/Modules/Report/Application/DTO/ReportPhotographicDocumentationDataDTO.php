<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class ReportPhotographicDocumentationDataDTO
{
    /** @param list<ReportImageDataDTO> $images */
    public function __construct(public array $images) {}
}
