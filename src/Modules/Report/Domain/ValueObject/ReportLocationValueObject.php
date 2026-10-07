<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

use src\Modules\Report\Domain\Entity\ReportImageEntity;

readonly class ReportLocationValueObject
{
    public function __construct(
        private ?ReportImageEntity $locationMap = null,
        private ?ReportImageEntity $municipalityInStateMap = null,
    ) {}

    public function locationMap(): ?ReportImageEntity
    {
        return $this->locationMap;
    }

    public function municipalityInStateMap(): ?ReportImageEntity
    {
        return $this->municipalityInStateMap;
    }
}
