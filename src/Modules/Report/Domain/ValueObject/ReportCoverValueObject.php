<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

use src\Modules\Report\Domain\Enum\ForceEnum;
use src\Modules\Report\Domain\Enum\ReportSizeEnum;
use src\Modules\Report\Domain\Validation\ReportCoverValidation;

readonly class ReportCoverValueObject
{
    public function __construct(
        private ?MunicipalityValueObject $municipality = null,
        private ?ForceEnum $force = null,
        private ?ReportSizeEnum $size = null,
        private string $typology = '',
        private ?SeiNumberValueObject $seiNumber = null,
    ) {
        ReportCoverValidation::validate($this);
    }

    public function municipality(): ?MunicipalityValueObject
    {
        return $this->municipality;
    }

    public function force(): ?ForceEnum
    {
        return $this->force;
    }

    public function size(): ?ReportSizeEnum
    {
        return $this->size;
    }

    public function typology(): string
    {
        return $this->typology;
    }

    public function seiNumber(): ?SeiNumberValueObject
    {
        return $this->seiNumber;
    }
}
