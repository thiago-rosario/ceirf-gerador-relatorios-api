<?php

declare(strict_types=1);

use src\Modules\Report\Domain\Enum\CodeExceptionEnum;
use src\Modules\Report\Domain\Exception\DuplicateReportFileException;
use src\Modules\Report\Domain\Exception\FutureInspectionDateException;
use src\Modules\Report\Domain\Exception\IncompleteReportException;
use src\Modules\Report\Domain\Exception\InvalidGeneratedReportException;
use src\Modules\Report\Domain\Exception\InvalidMunicipalityException;
use src\Modules\Report\Domain\Exception\InvalidReportAttachmentException;
use src\Modules\Report\Domain\Exception\InvalidReportCollaboratorsException;
use src\Modules\Report\Domain\Exception\InvalidReportConclusionException;
use src\Modules\Report\Domain\Exception\InvalidReportDateException;
use src\Modules\Report\Domain\Exception\InvalidReportFileReferenceException;
use src\Modules\Report\Domain\Exception\InvalidReportIdException;
use src\Modules\Report\Domain\Exception\InvalidReportImageCaptionException;
use src\Modules\Report\Domain\Exception\InvalidReportImageFormatException;
use src\Modules\Report\Domain\Exception\InvalidReportImageOrderException;
use src\Modules\Report\Domain\Exception\InvalidReportRevisionException;
use src\Modules\Report\Domain\Exception\InvalidReportStatusException;
use src\Modules\Report\Domain\Exception\InvalidReportTypologyException;
use src\Modules\Report\Domain\Exception\InvalidSeiNumberException;
use src\Modules\Report\Domain\Exception\ReportAlreadyGeneratedException;
use src\Modules\Report\Domain\Exception\ReportAttachmentDescriptionRequiredException;
use src\Modules\Report\Domain\Exception\ReportImageTooLargeException;
use src\Modules\Report\Domain\Exception\ReportNotGeneratedException;
use src\Modules\Report\Domain\Exception\TooManyReportFiguresException;

/**
 * Os códigos são contratos públicos de erro; os valores esperados não são derivados do enum.
 */
test('assigns the stable Report code to each domain exception', function (string $exceptionClass, int $expectedCode): void {
    $exception = new $exceptionClass;

    expect($exception->getCode())->toBe($expectedCode);
    expect(CodeExceptionEnum::from($expectedCode)->value)->toBe($exception->getCode());
})->with([
    'InvalidReportIdException' => [InvalidReportIdException::class, 2001],
    'InvalidReportRevisionException' => [InvalidReportRevisionException::class, 2002],
    'ReportAlreadyGeneratedException' => [ReportAlreadyGeneratedException::class, 2003],
    'ReportNotGeneratedException' => [ReportNotGeneratedException::class, 2004],
    'IncompleteReportException' => [IncompleteReportException::class, 2005],
    'InvalidMunicipalityException' => [InvalidMunicipalityException::class, 2006],
    'InvalidReportTypologyException' => [InvalidReportTypologyException::class, 2007],
    'InvalidSeiNumberException' => [InvalidSeiNumberException::class, 2008],
    'FutureInspectionDateException' => [FutureInspectionDateException::class, 2009],
    'InvalidReportCollaboratorsException' => [InvalidReportCollaboratorsException::class, 2010],
    'InvalidReportFileReferenceException' => [InvalidReportFileReferenceException::class, 2011],
    'ReportImageTooLargeException' => [ReportImageTooLargeException::class, 2012],
    'InvalidReportImageFormatException' => [InvalidReportImageFormatException::class, 2013],
    'DuplicateReportFileException' => [DuplicateReportFileException::class, 2014],
    'TooManyReportFiguresException' => [TooManyReportFiguresException::class, 2015],
    'InvalidReportImageOrderException' => [InvalidReportImageOrderException::class, 2016],
    'ReportAttachmentDescriptionRequiredException' => [ReportAttachmentDescriptionRequiredException::class, 2017],
    'InvalidReportConclusionException' => [InvalidReportConclusionException::class, 2018],
    'InvalidReportStatusException' => [InvalidReportStatusException::class, 2019],
    'InvalidReportDateException' => [InvalidReportDateException::class, 2020],
    'InvalidReportImageCaptionException' => [InvalidReportImageCaptionException::class, 2021],
    'InvalidGeneratedReportException' => [InvalidGeneratedReportException::class, 2022],
    'InvalidReportAttachmentException' => [InvalidReportAttachmentException::class, 2023],
]);
