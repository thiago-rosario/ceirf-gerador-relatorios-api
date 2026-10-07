<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Validation;

use src\Modules\Report\Domain\Exception\InvalidGeneratedReportException;
use src\Modules\Report\Domain\ValueObject\GeneratedReportValueObject;

final class GeneratedReportValidation
{
    public static function validate(GeneratedReportValueObject $document): void
    {
        $fileName = $document->fileName();

        if (ReportTextValidation::isBlank($document->storageIdentifier())
            || ReportTextValidation::isBlank($fileName)
            || str_contains($fileName, '/')
            || str_contains($fileName, '\\')
            || str_contains($fileName, "\0")
            || ! str_ends_with($fileName, '.pdf')
            || $fileName === '.pdf') {
            throw new InvalidGeneratedReportException;
        }
    }
}
