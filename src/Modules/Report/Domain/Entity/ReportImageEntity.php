<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Entity;

use src\Modules\Report\Domain\Trait\ResolvesReportValuesTrait;
use src\Modules\Report\Domain\Validation\ReportImageValidation;
use src\Modules\Report\Domain\ValueObject\ReportFileReferenceValueObject;
use src\Modules\Shared\Resolver\UuidResolver;

/**
 * Uma figura mantém sua identidade, arquivo e ordem desde o upload.
 * A numeração apresentada no documento é derivada da composição final.
 */
class ReportImageEntity
{
    use ResolvesReportValuesTrait;

    private UuidResolver $id;

    public function __construct(
        private ReportFileReferenceValueObject $file,
        private int $order,
        private string $caption = '',
        UuidResolver|string|null $id = null,
    ) {
        $this->id = $this->resolveUuid($id);

        ReportImageValidation::validate($this);
    }

    public function id(): UuidResolver
    {
        return $this->id;
    }

    public function file(): ReportFileReferenceValueObject
    {
        return $this->file;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function caption(): string
    {
        return $this->caption;
    }

    public function withCaption(string $caption): self
    {
        return new self($this->file, $this->order, $caption, $this->id);
    }
}
