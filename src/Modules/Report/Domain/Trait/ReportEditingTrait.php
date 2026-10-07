<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Trait;

use DateTimeImmutable;
use src\Modules\Report\Domain\Exception\ReportAlreadyGeneratedException;
use src\Modules\Report\Domain\Validation\ReportMediaValidation;

trait ReportEditingTrait
{
    private function ensureEditable(): void
    {
        if ($this->isGenerated()) {
            throw new ReportAlreadyGeneratedException;
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }

    /**
     * Retém a identidade e a ordem dos uploads removidos para impedir reordenação por reenvio.
     */
    private function rememberUploadedImages(): void
    {
        foreach (ReportMediaValidation::images($this) as $image) {
            foreach ($this->uploadedImages as $previousImage) {
                if (strcasecmp($image->id()->value(), $previousImage->id()->value()) === 0
                    || $image->file()->checksum() === $previousImage->file()->checksum()) {
                    continue 2;
                }
            }

            $this->uploadedImages[] = $image;
        }
    }
}
