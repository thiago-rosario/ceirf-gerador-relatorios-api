<?php

declare(strict_types=1);

use src\Modules\Report\Domain\Exception\InvalidGeneratedReportException;
use src\Modules\Report\Domain\ValueObject\GeneratedReportValueObject;

test('represents a generated PDF independently of its storage provider', function (): void {
    $generatedAt = new DateTimeImmutable('2026-10-06 09:00:00-03:00');

    $document = new GeneratedReportValueObject('institutional:report:123', 'SALVADOR_PM_1_TIPO_REV1.pdf', $generatedAt);

    expect($document->storageIdentifier())->toBe('institutional:report:123');
    expect($document->fileName())->toBe('SALVADOR_PM_1_TIPO_REV1.pdf');
    expect($document->generatedAt())->toBe($generatedAt);
});

test('rejects invalid generated document references with code 2022', function (string $identifier, string $fileName): void {
    $generatedAt = new DateTimeImmutable('2026-10-06 09:00:00-03:00');

    expect(fn () => new GeneratedReportValueObject($identifier, $fileName, $generatedAt))
        ->toThrow(function (InvalidGeneratedReportException $exception): void {
            expect($exception->getCode())->toBe(2022);
        });
})->with([
    'missing identifier' => ['', 'report.pdf'],
    'blank identifier' => ["\u{00A0}", 'report.pdf'],
    'missing filename' => ['object:report', ''],
    'not a PDF' => ['object:report', 'report.docx'],
    'missing basename' => ['object:report', '.pdf'],
    'directory' => ['object:report', 'reports/report.pdf'],
    'parent traversal' => ['object:report', '../report.pdf'],
    'Windows directory' => ['object:report', 'reports\\report.pdf'],
    'null byte' => ['object:report', "report\0.pdf"],
]);
