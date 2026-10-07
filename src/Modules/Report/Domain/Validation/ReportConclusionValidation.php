<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Validation;

use src\Modules\Report\Domain\Exception\InvalidReportConclusionException;
use src\Modules\Report\Domain\ValueObject\ReportConclusionValueObject;

final class ReportConclusionValidation
{
    public static function validateForGeneration(ReportConclusionValueObject $conclusion): void
    {
        if (ReportTextValidation::isBlank($conclusion->content())) {
            throw new InvalidReportConclusionException;
        }
    }
}
