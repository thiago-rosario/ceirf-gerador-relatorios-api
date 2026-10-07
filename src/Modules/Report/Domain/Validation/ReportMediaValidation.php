<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Validation;

use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Entity\ReportImageEntity;
use src\Modules\Report\Domain\Exception\DuplicateReportFileException;
use src\Modules\Report\Domain\Exception\InvalidReportImageOrderException;
use src\Modules\Report\Domain\Exception\TooManyReportFiguresException;
use src\Modules\Report\Domain\ValueObject\ReportFileReferenceValueObject;

final class ReportMediaValidation
{
    public const int MAXIMUM_FIGURES = 20;

    /**
     * Valida a sequência de upload da documentação fotográfica.
     * As demais seções possuem posições próprias na composição do documento.
     *
     * @param  array<array-key, mixed>  $images
     */
    public static function validateImages(array $images): void
    {
        if (! array_is_list($images)) {
            throw new InvalidReportImageOrderException;
        }

        if (count($images) > self::MAXIMUM_FIGURES) {
            throw new TooManyReportFiguresException;
        }

        $identifiers = [];
        $checksums = [];
        $lastOrder = 0;

        foreach ($images as $image) {
            if (! $image instanceof ReportImageEntity) {
                throw new InvalidReportImageOrderException('A documentação fotográfica deve conter somente figuras do relatório.');
            }

            $identifier = strtolower($image->id()->value());
            $checksum = $image->file()->checksum();

            if (isset($identifiers[$identifier]) || isset($checksums[$checksum])) {
                throw new DuplicateReportFileException;
            }

            if ($image->order() <= $lastOrder) {
                throw new InvalidReportImageOrderException;
            }

            $identifiers[$identifier] = true;
            $checksums[$checksum] = true;
            $lastOrder = $image->order();
        }
    }

    /**
     * @return list<ReportImageEntity>
     */
    public static function images(ReportEntity $report): array
    {
        $images = [];
        $locationMap = $report->location()?->locationMap();
        $municipalityInStateMap = $report->location()?->municipalityInStateMap();
        $preImplementationImage = $report->preImplementation()?->image();

        if ($locationMap !== null) {
            $images[] = $locationMap;
        }

        if ($municipalityInStateMap !== null) {
            $images[] = $municipalityInStateMap;
        }

        if ($preImplementationImage !== null) {
            $images[] = $preImplementationImage;
        }

        return [...$images, ...($report->photographicDocumentation()?->images() ?? [])];
    }

    /**
     * Inclui anexos visuais na quantidade total de figuras; imagens fixas da capa
     * pertencem ao template e não fazem parte do conteúdo editável do agregado.
     *
     * @return list<ReportFileReferenceValueObject>
     */
    public static function files(ReportEntity $report): array
    {
        $files = [];

        foreach (self::images($report) as $image) {
            $files[] = $image->file();
        }

        foreach ($report->attachments()?->files() ?? [] as $attachment) {
            $files[] = $attachment->file();
        }

        return $files;
    }

    public static function validate(ReportEntity $report): void
    {
        $files = self::files($report);

        if (count($files) > self::MAXIMUM_FIGURES) {
            throw new TooManyReportFiguresException;
        }

        $checksums = [];

        foreach ($files as $file) {
            if (isset($checksums[$file->checksum()])) {
                throw new DuplicateReportFileException;
            }

            $checksums[$file->checksum()] = true;
        }

        $identifiers = [];
        $media = [...self::images($report), ...($report->attachments()?->files() ?? [])];

        foreach ($media as $item) {
            $identifier = strtolower($item->id()->value());

            if (isset($identifiers[$identifier])) {
                throw new DuplicateReportFileException;
            }

            $identifiers[$identifier] = true;
        }
    }

    /**
     * Uma troca de legenda é permitida, mas não altera o upload original.
     * O histórico recebido também impede remover e reincluir para mudar a ordem.
     *
     * @param  list<ReportImageEntity>  $previous
     * @param  list<ReportImageEntity>  $replacement
     */
    public static function validateReplacement(array $previous, array $replacement): void
    {
        foreach ($replacement as $image) {
            foreach ($previous as $uploadedImage) {
                if (strcasecmp($image->id()->value(), $uploadedImage->id()->value()) !== 0
                    && $image->file()->checksum() !== $uploadedImage->file()->checksum()) {
                    continue;
                }

                if (strcasecmp($image->id()->value(), $uploadedImage->id()->value()) !== 0
                    || $image->order() !== $uploadedImage->order()
                    || ! $image->file()->equals($uploadedImage->file())) {
                    throw new InvalidReportImageOrderException('A identidade, a ordem e o arquivo da figura não podem ser alterados após o upload.');
                }
            }
        }
    }
}
