<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class ReportAttachmentsDataDTO
{
    /** @param list<ReportAttachmentDataDTO> $others */
    public function __construct(
        public ?ReportChecklistDataDTO $checklist,
        public ?ReportAttachmentDataDTO $municipalityLocationMap,
        public ?ReportAttachmentDataDTO $topographicPlan,
        public array $others,
    ) {}
}
