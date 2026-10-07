<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Service;

use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Exception\IncompleteReportException;
use src\Modules\Report\Domain\Exception\InvalidGeneratedReportException;
use src\Modules\Report\Domain\Trait\NormalizesReportFileNameTrait;
use src\Modules\Report\Domain\Validation\ReportCoverValidation;

final class ReportFileNameService
{
    use NormalizesReportFileNameTrait;

    public static function compose(ReportEntity $report): string
    {
        $cover = $report->cover();

        if ($cover === null) {
            throw new IncompleteReportException('A capa deve estar preenchida para compor o nome do documento.');
        }

        ReportCoverValidation::validateForGeneration($cover);

        $municipality = $cover->municipality() ?? throw new IncompleteReportException;
        $force = $cover->force() ?? throw new IncompleteReportException;
        $size = $cover->size() ?? throw new IncompleteReportException;

        $parts = [
            $municipality->name(),
            $force->value,
            $size->value,
            $cover->typology(),
        ];

        foreach ($parts as &$part) {
            $part = self::normalizeFileNamePart($part);

            if ($part === '') {
                throw new InvalidGeneratedReportException;
            }
        }
        unset($part);

        if ($report->revisionLabel() !== null) {
            $parts[] = $report->revisionLabel();
        }

        return implode('_', $parts).'.pdf';
    }
}
