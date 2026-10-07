<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Validation;

use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Entity\ReportImageEntity;
use src\Modules\Report\Domain\Enum\ReportStatusEnum;
use src\Modules\Report\Domain\Exception\DuplicateReportFileException;
use src\Modules\Report\Domain\Exception\IncompleteReportException;
use src\Modules\Report\Domain\Exception\InvalidGeneratedReportException;
use src\Modules\Report\Domain\Exception\InvalidReportImageOrderException;
use src\Modules\Report\Domain\Exception\InvalidReportRevisionException;
use src\Modules\Report\Domain\Exception\InvalidReportStatusException;

final class ReportValidation
{
    /**
     * Validade estrutural permite seções ausentes e respostas parciais em rascunhos.
     */
    public static function validate(ReportEntity $report): void
    {
        self::validateRevision($report);

        if (($report->status() === ReportStatusEnum::GENERATED) !== $report->isGenerated()) {
            throw new InvalidReportStatusException;
        }

        if ($report->cover() !== null) {
            ReportCoverValidation::validate($report->cover());
        }

        if ($report->generalInformation() !== null) {
            ReportGeneralInformationValidation::validate($report->generalInformation());
        }

        self::validateUploadHistory($report->uploadedImages());
        ReportMediaValidation::validate($report);
        ReportMediaValidation::validateReplacement($report->uploadedImages(), ReportMediaValidation::images($report));

        if ($report->generatedDocument() !== null
            && $report->generatedDocument()->fileName() !== $report->fileName()) {
            throw new InvalidGeneratedReportException;
        }
    }

    public static function validateRevision(ReportEntity $report): void
    {
        $id = strtolower($report->id()->value());
        $rootId = strtolower($report->rootReportId()->value());
        $parentId = $report->parentReportId() === null ? null : strtolower($report->parentReportId()->value());
        $revision = $report->revisionNumber();

        if ($revision < 0
            || ($revision === 0 && ($id !== $rootId || $parentId !== null))
            || ($revision > 0 && ($id === $rootId || $parentId === null || $id === $parentId))
            || ($revision === 1 && $parentId !== $rootId)
            || ($revision > 1 && $parentId === $rootId)) {
            throw new InvalidReportRevisionException;
        }
    }

    /**
     * @param  array<array-key, mixed>  $images
     */
    public static function validateUploadHistory(array $images): void
    {
        if (! array_is_list($images)) {
            throw new InvalidReportImageOrderException;
        }

        $identifiers = [];
        $checksums = [];

        foreach ($images as $image) {
            if (! $image instanceof ReportImageEntity) {
                throw new InvalidReportImageOrderException;
            }

            $identifier = strtolower($image->id()->value());
            $checksum = $image->file()->checksum();

            if (isset($identifiers[$identifier]) || isset($checksums[$checksum])) {
                throw new DuplicateReportFileException;
            }

            $identifiers[$identifier] = true;
            $checksums[$checksum] = true;
        }
    }

    /**
     * Completa as obrigatoriedades conhecidas antes de gerar ou restaurar uma versão final.
     */
    public static function validateForGeneration(ReportEntity $report): void
    {
        self::validate($report);
        $cover = $report->cover();
        $generalInformation = $report->generalInformation();
        $infrastructure = $report->infrastructure();
        $checklist = $report->attachments()?->checklist();
        $conclusion = $report->conclusion();

        if ($cover === null || $generalInformation === null || $infrastructure === null
            || $checklist === null || $conclusion === null
            || $report->location()?->municipalityInStateMap() === null) {
            throw new IncompleteReportException;
        }

        ReportCoverValidation::validateForGeneration($cover);
        ReportGeneralInformationValidation::validateForGeneration($generalInformation);
        ReportChecklistValidation::validateForGeneration($infrastructure);
        ReportChecklistValidation::validateForGeneration($checklist);
        ReportConclusionValidation::validateForGeneration($conclusion);

        foreach ($report->attachments()->files() as $attachment) {
            ReportAttachmentValidation::validateForGeneration($attachment);
        }
    }
}
