<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

use DateTimeImmutable;

readonly class ReportGeneralInformationDataDTO
{
    public function __construct(
        public ?DateTimeImmutable $inspectionDate,
        public string $collaborators,
    ) {}
}
