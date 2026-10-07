<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Entity;

use src\Modules\Report\Domain\Enum\ReportAttachmentTypeEnum;
use src\Modules\Report\Domain\Trait\ResolvesReportValuesTrait;
use src\Modules\Report\Domain\Validation\ReportAttachmentValidation;
use src\Modules\Report\Domain\ValueObject\ReportFileReferenceValueObject;
use src\Modules\Shared\Resolver\UuidResolver;

class ReportAttachmentEntity
{
    use ResolvesReportValuesTrait;

    private UuidResolver $id;

    public function __construct(
        private ReportAttachmentTypeEnum $type,
        private ReportFileReferenceValueObject $file,
        private string $description = '',
        UuidResolver|string|null $id = null,
    ) {
        $this->id = $this->resolveUuid($id);

        ReportAttachmentValidation::validate($this);
    }

    public function id(): UuidResolver
    {
        return $this->id;
    }

    public function type(): ReportAttachmentTypeEnum
    {
        return $this->type;
    }

    public function file(): ReportFileReferenceValueObject
    {
        return $this->file;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function withDescription(string $description): self
    {
        return new self($this->type, $this->file, $description, $this->id);
    }
}
