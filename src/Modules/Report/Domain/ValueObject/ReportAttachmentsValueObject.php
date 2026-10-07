<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

use src\Modules\Report\Domain\Entity\ReportAttachmentEntity;
use src\Modules\Report\Domain\Validation\ReportAttachmentValidation;

readonly class ReportAttachmentsValueObject
{
    /**
     * @var list<ReportAttachmentEntity>
     */
    private array $others;

    /**
     * @param  list<ReportAttachmentEntity>  $others
     */
    public function __construct(
        private ?ReportChecklistValueObject $checklist = null,
        private ?ReportAttachmentEntity $municipalityLocationMap = null,
        private ?ReportAttachmentEntity $topographicPlan = null,
        array $others = [],
    ) {
        ReportAttachmentValidation::validateOthers($others);

        $this->others = $others;

        ReportAttachmentValidation::validateCollection($this);
    }

    public function checklist(): ?ReportChecklistValueObject
    {
        return $this->checklist;
    }

    public function municipalityLocationMap(): ?ReportAttachmentEntity
    {
        return $this->municipalityLocationMap;
    }

    public function topographicPlan(): ?ReportAttachmentEntity
    {
        return $this->topographicPlan;
    }

    /**
     * @return list<ReportAttachmentEntity>
     */
    public function others(): array
    {
        return $this->others;
    }

    /**
     * @return list<ReportAttachmentEntity>
     */
    public function files(): array
    {
        $files = [];

        if ($this->municipalityLocationMap !== null) {
            $files[] = $this->municipalityLocationMap;
        }

        if ($this->topographicPlan !== null) {
            $files[] = $this->topographicPlan;
        }

        return [...$files, ...$this->others];
    }
}
