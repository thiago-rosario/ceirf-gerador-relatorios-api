<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

use src\Modules\Report\Domain\Entity\ReportImageEntity;
use src\Modules\Report\Domain\Validation\ReportMediaValidation;

readonly class ReportPhotographicDocumentationValueObject
{
    /**
     * @var list<ReportImageEntity>
     */
    private array $images;

    /**
     * @param  list<ReportImageEntity>  $images  Fotografias na ordem de inclusão.
     */
    public function __construct(array $images = [])
    {
        ReportMediaValidation::validateImages($images);

        $this->images = $images;
    }

    /**
     * @return list<ReportImageEntity>
     */
    public function images(): array
    {
        return $this->images;
    }

    public function append(ReportImageEntity $image): self
    {
        return new self([...$this->images, $image]);
    }
}
