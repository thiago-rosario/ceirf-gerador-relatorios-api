<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

use src\Modules\Report\Domain\Validation\ReportFileReferenceValidation;

/**
 * Metadados de um arquivo já recebido, sem conhecer seu meio de armazenamento.
 * O checksum SHA-256 identifica o conteúdo para prevenção de duplicidade.
 */
readonly class ReportFileReferenceValueObject
{
    private string $checksum;

    public function __construct(
        private string $storageIdentifier,
        private string $fileName,
        private string $mimeType,
        private int $sizeBytes,
        string $checksum,
    ) {
        $this->checksum = strtolower(trim($checksum));

        ReportFileReferenceValidation::validate($this);
    }

    public function storageIdentifier(): string
    {
        return $this->storageIdentifier;
    }

    public function fileName(): string
    {
        return $this->fileName;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function sizeBytes(): int
    {
        return $this->sizeBytes;
    }

    public function checksum(): string
    {
        return $this->checksum;
    }

    public function equals(self $other): bool
    {
        return $this->storageIdentifier === $other->storageIdentifier
            && $this->fileName === $other->fileName
            && $this->mimeType === $other->mimeType
            && $this->sizeBytes === $other->sizeBytes
            && $this->checksum === $other->checksum;
    }
}
