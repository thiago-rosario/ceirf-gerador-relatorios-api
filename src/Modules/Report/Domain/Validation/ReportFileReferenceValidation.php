<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Validation;

use src\Modules\Report\Domain\Exception\InvalidReportFileReferenceException;
use src\Modules\Report\Domain\ValueObject\ReportFileReferenceValueObject;

final class ReportFileReferenceValidation
{
    public static function validate(ReportFileReferenceValueObject $file): void
    {
        if (ReportTextValidation::isBlank($file->storageIdentifier())
            || ReportTextValidation::isBlank($file->fileName())
            || ReportTextValidation::isBlank($file->mimeType())
            || $file->sizeBytes() <= 0
            || preg_match('/\A[a-f0-9]{64}\z/', $file->checksum()) !== 1) {
            throw new InvalidReportFileReferenceException;
        }
    }
}
