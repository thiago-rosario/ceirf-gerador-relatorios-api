<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

use DateTimeImmutable;
use src\Modules\Report\Domain\Validation\ReportGeneralInformationValidation;

readonly class ReportGeneralInformationValueObject
{
    public function __construct(
        private ?DateTimeImmutable $inspectionDate = null,
        private string $collaborators = '',
    ) {
        ReportGeneralInformationValidation::validate($this);
    }

    public function inspectionDate(): ?DateTimeImmutable
    {
        return $this->inspectionDate;
    }

    public function collaborators(): string
    {
        return $this->collaborators;
    }
}
