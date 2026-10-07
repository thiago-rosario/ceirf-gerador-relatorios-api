<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

use src\Modules\Report\Domain\Entity\ReportImageEntity;

/**
 * A legenda e a observação institucionais pertencem ao template do documento.
 */
readonly class ReportPreImplementationValueObject
{
    public function __construct(private ?ReportImageEntity $image = null) {}

    public function image(): ?ReportImageEntity
    {
        return $this->image;
    }
}
