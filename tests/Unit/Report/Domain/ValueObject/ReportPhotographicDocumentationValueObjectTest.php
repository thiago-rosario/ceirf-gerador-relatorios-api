<?php

declare(strict_types=1);

use src\Modules\Report\Domain\Entity\ReportImageEntity;
use src\Modules\Report\Domain\Exception\DuplicateReportFileException;
use src\Modules\Report\Domain\Exception\InvalidReportImageOrderException;
use src\Modules\Report\Domain\Validation\ReportMediaValidation;
use src\Modules\Report\Domain\ValueObject\ReportFileReferenceValueObject;
use src\Modules\Report\Domain\ValueObject\ReportPhotographicDocumentationValueObject;

test('appends a photograph while preserving the previous collection and upload order', function (): void {
    $firstFile = new ReportFileReferenceValueObject('object:first', 'first.png', 'image/png', 100, str_repeat('a', 64));
    $secondFile = new ReportFileReferenceValueObject('object:second', 'second.png', 'image/png', 100, str_repeat('b', 64));
    $firstImage = new ReportImageEntity($firstFile, 1, id: '00000000-0000-4000-8000-000000000001');
    $secondImage = new ReportImageEntity($secondFile, 3, id: '00000000-0000-4000-8000-000000000002');
    $photographs = new ReportPhotographicDocumentationValueObject([$firstImage]);

    $updatedPhotographs = $photographs->append($secondImage);

    expect($photographs->images())->toBe([$firstImage]);
    expect($updatedPhotographs->images())->toBe([$firstImage, $secondImage]);
});

test('rejects duplicate content or upload identities with code 2014', function (string $secondHash, string $secondId): void {
    $firstFile = new ReportFileReferenceValueObject('object:first', 'first.png', 'image/png', 100, str_repeat('a', 64));
    $secondFile = new ReportFileReferenceValueObject('object:second', 'second.png', 'image/png', 100, $secondHash);
    $firstImage = new ReportImageEntity($firstFile, 1, id: '00000000-0000-4000-8000-000000000001');
    $secondImage = new ReportImageEntity($secondFile, 2, id: $secondId);

    expect(fn () => new ReportPhotographicDocumentationValueObject([$firstImage, $secondImage]))
        ->toThrow(function (DuplicateReportFileException $exception): void {
            expect($exception->getCode())->toBe(2014);
        });
})->with([
    'same content under a new ID' => [str_repeat('a', 64), '00000000-0000-4000-8000-000000000002'],
    'same ID for different contents' => [str_repeat('b', 64), '00000000-0000-4000-8000-000000000001'],
]);

test('rejects repeated or descending photographic upload order with code 2016', function (int $firstOrder, int $secondOrder): void {
    $firstFile = new ReportFileReferenceValueObject('object:first', 'first.png', 'image/png', 100, str_repeat('a', 64));
    $secondFile = new ReportFileReferenceValueObject('object:second', 'second.png', 'image/png', 100, str_repeat('b', 64));
    $firstImage = new ReportImageEntity($firstFile, $firstOrder, id: '00000000-0000-4000-8000-000000000001');
    $secondImage = new ReportImageEntity($secondFile, $secondOrder, id: '00000000-0000-4000-8000-000000000002');

    expect(fn () => new ReportPhotographicDocumentationValueObject([$firstImage, $secondImage]))
        ->toThrow(function (InvalidReportImageOrderException $exception): void {
            expect($exception->getCode())->toBe(2016);
        });
})->with([
    'repeated' => [1, 1],
    'descending' => [2, 1],
]);

test('rejects changing an uploaded figure order even under a new ID with code 2016', function (): void {
    $file = new ReportFileReferenceValueObject('object:first', 'first.png', 'image/png', 100, str_repeat('a', 64));
    $uploadedImage = new ReportImageEntity($file, 1, id: '00000000-0000-4000-8000-000000000001');
    $replacement = new ReportImageEntity($file, 2, id: '00000000-0000-4000-8000-000000000002');

    expect(fn () => ReportMediaValidation::validateReplacement([$uploadedImage], [$replacement]))
        ->toThrow(function (InvalidReportImageOrderException $exception): void {
            expect($exception->getCode())->toBe(2016);
        });
});
