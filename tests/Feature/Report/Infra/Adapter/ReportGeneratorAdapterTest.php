<?php

declare(strict_types=1);

use Dompdf\Dompdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use src\Modules\Report\Domain\Entity\ReportImageEntity;
use src\Modules\Report\Domain\Exception\IncompleteReportException;
use src\Modules\Report\Domain\Exception\InvalidReportFileReferenceException;
use src\Modules\Report\Domain\ValueObject\ReportFileReferenceValueObject;
use src\Modules\Report\Domain\ValueObject\ReportLocationValueObject;
use src\Modules\Report\Infra\Adapter\ReportGeneratorAdapter;
use src\Modules\Report\Infra\Service\ReportPdfMediaService;
use Tests\Fixtures\ReportApplicationFixtures;

/**
 * @param  array{storageIdentifier?: string, mimeType?: string, sizeBytes?: int, checksum?: string}  $overrides
 */
function storedPdfReportImage(array $overrides = [], string $extension = 'png'): ReportImageEntity
{
    $storageIdentifier = 'reports/550e8400-e29b-41d4-a716-446655440010/media/550e8400-e29b-41d4-a716-446655440011.'.$extension;
    $upload = UploadedFile::fake()->image('municipality.'.$extension, 80, 60);
    $contents = $upload->getContent();
    Storage::disk('local')->put($storageIdentifier, $contents);

    return new ReportImageEntity(
        file: new ReportFileReferenceValueObject(...array_replace([
            'storageIdentifier' => $storageIdentifier,
            'fileName' => 'municipality.'.$extension,
            'mimeType' => $extension === 'jpg' ? 'image/jpeg' : 'image/png',
            'sizeBytes' => strlen($contents),
            'checksum' => hash('sha256', $contents),
        ], $overrides)),
        order: 1,
        caption: 'Município de Salvador',
        id: '550e8400-e29b-41d4-a716-446655440011',
    );
}

function storedPdfReportDocumentImage(): ReportImageEntity
{
    $sourcePdf = new Dompdf;
    $sourcePdf->loadHtml('<p>Primeira página do mapa</p><p style="page-break-before: always">Segunda página do mapa</p>');
    $sourcePdf->render();
    $contents = $sourcePdf->output();
    $storageIdentifier = 'reports/550e8400-e29b-41d4-a716-446655440010/media/550e8400-e29b-41d4-a716-446655440011.pdf';
    Storage::disk('local')->put($storageIdentifier, $contents);

    return new ReportImageEntity(
        file: new ReportFileReferenceValueObject(
            storageIdentifier: $storageIdentifier,
            fileName: 'municipality.pdf',
            mimeType: 'application/pdf',
            sizeBytes: strlen($contents),
            checksum: hash('sha256', $contents),
        ),
        order: 1,
        id: '550e8400-e29b-41d4-a716-446655440011',
    );
}

test('stores a real PDF with its final name without changing the draft or its source image', function (string $extension): void {
    Storage::fake('local');
    $this->travelTo(new DateTimeImmutable('2020-01-02 10:30:00'));
    $image = storedPdfReportImage(extension: $extension);
    $report = ReportApplicationFixtures::completeReport([
        'location' => new ReportLocationValueObject(municipalityInStateMap: $image),
    ]);
    $originalImage = serialize($image);
    $originalUpdatedAt = $report->updatedAt();

    $document = app(ReportGeneratorAdapter::class)->generate($report);

    Storage::disk('local')->assertExists($document->storageIdentifier());
    expect(Storage::disk('local')->get($document->storageIdentifier()))->toStartWith('%PDF-');
    expect($document->storageIdentifier())->toStartWith('reports/'.$report->id()->value().'/documents/')->toEndWith('.pdf');
    expect($document->fileName())->toBe('SALVADOR_PM_1B_DELEGACIA.pdf');
    expect($document->generatedAt()->format('Y-m-d H:i:s'))->toBe('2020-01-02 10:30:00');
    expect($report->isGenerated())->toBeFalse();
    expect($report->generatedDocument())->toBeNull();
    expect($report->updatedAt())->toBe($originalUpdatedAt);
    expect(serialize($image))->toBe($originalImage);
})->with(['PNG' => ['png'], 'JPEG' => ['jpg']]);

test('converts every page of a PDF source to printable images and removes the conversion files', function (): void {
    Storage::fake('local');
    $image = storedPdfReportDocumentImage();
    $report = ReportApplicationFixtures::completeReport([
        'location' => new ReportLocationValueObject(municipalityInStateMap: $image),
    ]);

    $media = app(ReportPdfMediaService::class)->resolve($report);

    expect($media[$image->file()->storageIdentifier()])->toHaveCount(2);
    expect($media[$image->file()->storageIdentifier()][0])->toStartWith('data:image/png;base64,');
    expect($media[$image->file()->storageIdentifier()][1])->toStartWith('data:image/png;base64,');
    expect(Storage::disk('local')->allFiles('reports/tmp'))->toBe([]);
});

test('does not create a document and removes temporary files when the PDF converter fails', function (): void {
    Storage::fake('local');
    config(['report_pdf.pdftoppm_binary' => Storage::disk('local')->path('unavailable-pdf-converter')]);
    $image = storedPdfReportDocumentImage();
    $report = ReportApplicationFixtures::completeReport([
        'location' => new ReportLocationValueObject(municipalityInStateMap: $image),
    ]);

    expect(fn () => app(ReportGeneratorAdapter::class)->generate($report))->toThrow(RuntimeException::class);

    expect(Storage::disk('local')->allFiles('reports/tmp'))->toBe([]);
    expect(Storage::disk('local')->allFiles('reports/'.$report->id()->value().'/documents'))->toBe([]);
});

test('does not create a document and removes temporary files when a PDF exceeds the configured page limit', function (): void {
    Storage::fake('local');
    config(['report_pdf.maximum_pdf_pages' => 1]);
    $image = storedPdfReportDocumentImage();
    $report = ReportApplicationFixtures::completeReport([
        'location' => new ReportLocationValueObject(municipalityInStateMap: $image),
    ]);

    expect(fn () => app(ReportGeneratorAdapter::class)->generate($report))->toThrow(InvalidReportFileReferenceException::class);

    expect(Storage::disk('local')->allFiles('reports/tmp'))->toBe([]);
    expect(Storage::disk('local')->allFiles('reports/'.$report->id()->value().'/documents'))->toBe([]);
});

test('does not create a document when the report is incomplete', function (): void {
    Storage::fake('local');
    $report = ReportApplicationFixtures::completeReport(['location' => new ReportLocationValueObject]);

    expect(fn () => app(ReportGeneratorAdapter::class)->generate($report))->toThrow(IncompleteReportException::class);

    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('does not create a document when a source file is missing', function (): void {
    Storage::fake('local');
    $image = storedPdfReportImage();
    Storage::disk('local')->delete($image->file()->storageIdentifier());
    $report = ReportApplicationFixtures::completeReport([
        'location' => new ReportLocationValueObject(municipalityInStateMap: $image),
    ]);

    expect(fn () => app(ReportGeneratorAdapter::class)->generate($report))->toThrow(InvalidReportFileReferenceException::class);

    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('does not create a document when the source contents changed after upload', function (): void {
    Storage::fake('local');
    $image = storedPdfReportImage();
    Storage::disk('local')->put($image->file()->storageIdentifier(), 'tampered image');
    $report = ReportApplicationFixtures::completeReport([
        'location' => new ReportLocationValueObject(municipalityInStateMap: $image),
    ]);

    expect(fn () => app(ReportGeneratorAdapter::class)->generate($report))->toThrow(InvalidReportFileReferenceException::class);

    expect(Storage::disk('local')->allFiles('reports/'.$report->id()->value().'/documents'))->toBe([]);
});

test('does not create a document when source metadata does not match its stored file', function (array $overrides): void {
    Storage::fake('local');
    $image = storedPdfReportImage($overrides);
    $report = ReportApplicationFixtures::completeReport([
        'location' => new ReportLocationValueObject(municipalityInStateMap: $image),
    ]);

    expect(fn () => app(ReportGeneratorAdapter::class)->generate($report))->toThrow(InvalidReportFileReferenceException::class);

    expect(Storage::disk('local')->allFiles('reports/'.$report->id()->value().'/documents'))->toBe([]);
})->with([
    'checksum' => [['checksum' => str_repeat('a', 64)]],
    'file size' => [['sizeBytes' => 1]],
    'MIME type' => [['mimeType' => 'image/jpeg']],
]);

test('does not read a stored source outside the report media directory', function (string $storageIdentifier): void {
    Storage::fake('local');
    $image = storedPdfReportImage(['storageIdentifier' => $storageIdentifier]);
    $validStorageIdentifier = 'reports/550e8400-e29b-41d4-a716-446655440010/media/550e8400-e29b-41d4-a716-446655440011.png';
    Storage::disk('local')->put($storageIdentifier, Storage::disk('local')->get($validStorageIdentifier));
    $report = ReportApplicationFixtures::completeReport([
        'location' => new ReportLocationValueObject(municipalityInStateMap: $image),
    ]);

    expect(fn () => app(ReportGeneratorAdapter::class)->generate($report))->toThrow(InvalidReportFileReferenceException::class);

    expect(Storage::disk('local')->allFiles('reports/'.$report->id()->value().'/documents'))->toBe([]);
})->with([
    'another report' => ['reports/550e8400-e29b-41d4-a716-446655440020/media/550e8400-e29b-41d4-a716-446655440011.png'],
    'a different directory' => ['private/550e8400-e29b-41d4-a716-446655440011.png'],
    'parent directory traversal' => ['reports/550e8400-e29b-41d4-a716-446655440010/media/../550e8400-e29b-41d4-a716-446655440011.png'],
]);
