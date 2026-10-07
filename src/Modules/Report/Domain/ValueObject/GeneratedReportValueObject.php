<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

use DateTimeImmutable;
use src\Modules\Report\Domain\Validation\GeneratedReportValidation;

/**
 * Referencia um PDF efetivamente gerado sem depender do provedor de armazenamento.
 */
readonly class GeneratedReportValueObject
{
    public function __construct(
        private string $storageIdentifier,
        private string $fileName,
        private DateTimeImmutable $generatedAt,
    ) {
        GeneratedReportValidation::validate($this);
    }

    public function storageIdentifier(): string
    {
        return $this->storageIdentifier;
    }

    public function fileName(): string
    {
        return $this->fileName;
    }

    public function generatedAt(): DateTimeImmutable
    {
        return $this->generatedAt;
    }
}
