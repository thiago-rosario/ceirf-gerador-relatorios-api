<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

use src\Modules\Report\Domain\Validation\ReportCoverValidation;

readonly class SeiNumberValueObject
{
    public function __construct(private string $value)
    {
        ReportCoverValidation::validateSeiNumber($this);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
