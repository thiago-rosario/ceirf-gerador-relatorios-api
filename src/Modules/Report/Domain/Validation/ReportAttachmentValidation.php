<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Validation;

use src\Modules\Report\Domain\Entity\ReportAttachmentEntity;
use src\Modules\Report\Domain\Enum\ReportAttachmentTypeEnum;
use src\Modules\Report\Domain\Exception\DuplicateReportFileException;
use src\Modules\Report\Domain\Exception\InvalidReportAttachmentException;
use src\Modules\Report\Domain\Exception\ReportAttachmentDescriptionRequiredException;
use src\Modules\Report\Domain\ValueObject\ReportAttachmentsValueObject;

final class ReportAttachmentValidation
{
    public static function validate(ReportAttachmentEntity $attachment): void
    {
        ReportImageValidation::validateFile($attachment->file());

        if (mb_strlen($attachment->description()) > 500) {
            throw new InvalidReportAttachmentException('A descrição do anexo deve possuir no máximo 500 caracteres.');
        }
    }

    public static function validateForGeneration(ReportAttachmentEntity $attachment): void
    {
        self::validate($attachment);

        if ($attachment->type() === ReportAttachmentTypeEnum::OTHER
            && ReportTextValidation::isBlank($attachment->description())) {
            throw new ReportAttachmentDescriptionRequiredException;
        }
    }

    public static function validateCollection(ReportAttachmentsValueObject $attachments): void
    {
        if ($attachments->municipalityLocationMap() !== null
            && $attachments->municipalityLocationMap()->type() !== ReportAttachmentTypeEnum::MUNICIPALITY_LOCATION_MAP) {
            throw new InvalidReportAttachmentException('O mapa de localização do município deve possuir o tipo correspondente.');
        }

        if ($attachments->topographicPlan() !== null
            && $attachments->topographicPlan()->type() !== ReportAttachmentTypeEnum::TOPOGRAPHIC_PLAN) {
            throw new InvalidReportAttachmentException('A planta topográfica deve possuir o tipo correspondente.');
        }

        $identifiers = [];
        $checksums = [];

        foreach ($attachments->files() as $attachment) {
            $identifier = strtolower($attachment->id()->value());
            $checksum = $attachment->file()->checksum();

            if (isset($identifiers[$identifier]) || isset($checksums[$checksum])) {
                throw new DuplicateReportFileException;
            }

            $identifiers[$identifier] = true;
            $checksums[$checksum] = true;
        }
    }

    /**
     * @param  array<array-key, mixed>  $attachments
     */
    public static function validateOthers(array $attachments): void
    {
        if (! array_is_list($attachments)) {
            throw new InvalidReportAttachmentException('Os outros anexos devem ser uma lista.');
        }

        foreach ($attachments as $attachment) {
            if (! $attachment instanceof ReportAttachmentEntity
                || $attachment->type() !== ReportAttachmentTypeEnum::OTHER) {
                throw new InvalidReportAttachmentException('Os outros anexos devem possuir o tipo correspondente.');
            }
        }
    }
}
