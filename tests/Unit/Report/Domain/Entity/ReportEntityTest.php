<?php

declare(strict_types=1);

use src\Modules\Identity\Domain\Exception\InvalidUserIdException;
use src\Modules\Report\Domain\Entity\ReportAttachmentEntity;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Entity\ReportImageEntity;
use src\Modules\Report\Domain\Enum\ChecklistAnswerEnum;
use src\Modules\Report\Domain\Enum\ForceEnum;
use src\Modules\Report\Domain\Enum\ReportAttachmentTypeEnum;
use src\Modules\Report\Domain\Enum\ReportSizeEnum;
use src\Modules\Report\Domain\Enum\ReportStatusEnum;
use src\Modules\Report\Domain\Exception\DuplicateReportFileException;
use src\Modules\Report\Domain\Exception\IncompleteReportException;
use src\Modules\Report\Domain\Exception\InvalidGeneratedReportException;
use src\Modules\Report\Domain\Exception\InvalidReportDateException;
use src\Modules\Report\Domain\Exception\InvalidReportIdException;
use src\Modules\Report\Domain\Exception\InvalidReportImageOrderException;
use src\Modules\Report\Domain\Exception\InvalidReportRevisionException;
use src\Modules\Report\Domain\Exception\InvalidReportStatusException;
use src\Modules\Report\Domain\Exception\ReportAlreadyGeneratedException;
use src\Modules\Report\Domain\Exception\ReportNotGeneratedException;
use src\Modules\Report\Domain\Exception\TooManyReportFiguresException;
use src\Modules\Report\Domain\ValueObject\GeneratedReportValueObject;
use src\Modules\Report\Domain\ValueObject\MunicipalityValueObject;
use src\Modules\Report\Domain\ValueObject\ReportAttachmentsValueObject;
use src\Modules\Report\Domain\ValueObject\ReportChecklistValueObject;
use src\Modules\Report\Domain\ValueObject\ReportConclusionValueObject;
use src\Modules\Report\Domain\ValueObject\ReportCoverValueObject;
use src\Modules\Report\Domain\ValueObject\ReportFileReferenceValueObject;
use src\Modules\Report\Domain\ValueObject\ReportGeneralInformationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportInfrastructureValueObject;
use src\Modules\Report\Domain\ValueObject\ReportLocationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportPhotographicDocumentationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportPreImplementationValueObject;
use src\Modules\Report\Domain\ValueObject\SeiNumberValueObject;
use src\Modules\Shared\Resolver\UuidResolver;

function aggregateReportFile(int $number): ReportFileReferenceValueObject
{
    return new ReportFileReferenceValueObject(
        storageIdentifier: sprintf('asset:%d', $number),
        fileName: sprintf('figure-%d.png', $number),
        mimeType: 'image/png',
        sizeBytes: 1024,
        checksum: hash('sha256', sprintf('figure-content-%d', $number)),
    );
}

function aggregateReportImage(int $number, ?int $order = null): ReportImageEntity
{
    return new ReportImageEntity(
        file: aggregateReportFile($number),
        order: $order ?? $number,
        caption: sprintf('Figura do terreno %d', $number),
        id: sprintf('550e8400-e29b-41d4-a716-%012d', $number),
    );
}

/**
 * @param  array<string, mixed>  $overrides
 */
function generationReadyAggregateReport(array $overrides = []): ReportEntity
{
    return new ReportEntity(...array_replace([
        'createdBy' => '550e8400-e29b-41d4-a716-446655440000',
        'id' => '550e8400-e29b-41d4-a716-446655440010',
        'createdAt' => '2020-01-01 08:00:00',
        'updatedAt' => '2020-01-01 08:00:00',
        'cover' => new ReportCoverValueObject(
            municipality: new MunicipalityValueObject(1, 'Salvador'),
            force: ForceEnum::PM,
            size: ReportSizeEnum::ONE_B,
            typology: 'Delegacia',
            seiNumber: new SeiNumberValueObject('012.3456.2020.0000012-34'),
        ),
        'generalInformation' => new ReportGeneralInformationValueObject(
            inspectionDate: new DateTimeImmutable('2020-01-01'),
            collaborators: 'Ana Silva e João Santos',
        ),
        'location' => new ReportLocationValueObject(municipalityInStateMap: aggregateReportImage(1)),
        'infrastructure' => new ReportInfrastructureValueObject(
            waterNetwork: ChecklistAnswerEnum::YES,
            highVoltageNetwork: ChecklistAnswerEnum::NO,
            lowVoltageNetwork: ChecklistAnswerEnum::YES,
            sewageNetwork: ChecklistAnswerEnum::NO,
            telephony: ChecklistAnswerEnum::NOT_APPLICABLE,
            publicLighting: ChecklistAnswerEnum::YES,
            internet: ChecklistAnswerEnum::YES,
            wasteCollection: ChecklistAnswerEnum::YES,
            paving: ChecklistAnswerEnum::NO,
            existingBuildings: ChecklistAnswerEnum::NO,
        ),
        'preImplementation' => new ReportPreImplementationValueObject,
        'photographicDocumentation' => new ReportPhotographicDocumentationValueObject,
        'attachments' => new ReportAttachmentsValueObject(checklist: new ReportChecklistValueObject(
            seiConstructionRequest: ChecklistAnswerEnum::YES,
            seiLandAndTypologyIdentification: ChecklistAnswerEnum::YES,
            stateOwnedLand: ChecklistAnswerEnum::YES,
            simovLegalized: ChecklistAnswerEnum::YES,
            compatibleDimensions: ChecklistAnswerEnum::YES,
            slopeOrLevelRisk: ChecklistAnswerEnum::NO,
            stormwaterDrainage: ChecklistAnswerEnum::YES,
            floodHistory: ChecklistAnswerEnum::NO,
            electricitySupply: ChecklistAnswerEnum::YES,
            waterSupply: ChecklistAnswerEnum::YES,
            sewageSupply: ChecklistAnswerEnum::NO,
            pavingAndSidewalk: ChecklistAnswerEnum::YES,
            regularWasteCollection: ChecklistAnswerEnum::YES,
            demolitionRequired: ChecklistAnswerEnum::NO,
            easyPublicAccess: ChecklistAnswerEnum::YES,
            domainStripOrNonBuildableArea: ChecklistAnswerEnum::NO,
            technicalFeasibilityReport: ChecklistAnswerEnum::YES,
            reportAttachedToSei: ChecklistAnswerEnum::YES,
            worksDashboardUpdated: ChecklistAnswerEnum::YES,
            environmentalProtectionArea: ChecklistAnswerEnum::NOT_APPLICABLE,
        )),
        'conclusion' => new ReportConclusionValueObject('O terreno apresenta condições adequadas à implantação.'),
    ], $overrides));
}

function aggregateGeneratedDocument(string $fileName = 'SALVADOR_PM_1B_DELEGACIA.pdf'): GeneratedReportValueObject
{
    return new GeneratedReportValueObject('document:final', $fileName, new DateTimeImmutable('2020-01-01 10:00:00'));
}

test('creates an incomplete original as an editable document family with its own UUID', function (): void {
    $author = new UuidResolver('550e8400-e29b-41d4-a716-446655440000');

    $report = new ReportEntity(createdBy: $author);
    $report->validate();

    expect($report->id()->value())->toBeUuid();
    expect($report->rootReportId()->value())->toBe($report->id()->value());
    expect($report->parentReportId())->toBeNull();
    expect($report->createdBy())->toBe($author);
    expect($report->revisionNumber())->toBe(0);
    expect($report->isRevision())->toBeFalse();
    expect($report->revisionLabel())->toBeNull();
    expect($report->status())->toBe(ReportStatusEnum::DRAFT);
    expect($report->generatedDocument())->toBeNull();
    expect($report->createdAt())->toBe($report->updatedAt());
});

test('wraps malformed report identities with the Report exception code', function (string $parameter): void {
    $arguments = ['createdBy' => '550e8400-e29b-41d4-a716-446655440000', $parameter => 'invalid-uuid'];

    expect(fn () => new ReportEntity(...$arguments))->toThrow(function (InvalidReportIdException $exception): void {
        expect($exception->getCode())->toBe(2001);
        expect($exception->getPrevious())->toBeInstanceOf(InvalidUserIdException::class);
    });
})->with(['id', 'rootReportId', 'parentReportId', 'createdBy']);

test('copies mutable report dates without retaining external mutable objects', function (): void {
    $createdAt = new DateTime('2020-01-01 08:00:00');
    $updatedAt = new DateTime('2020-01-02 09:00:00');
    $report = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        createdAt: $createdAt,
        updatedAt: $updatedAt,
    );

    $createdAt->modify('+1 year');
    $updatedAt->modify('+1 year');

    expect($report->createdAt())->toBeInstanceOf(DateTimeImmutable::class);
    expect($report->createdAt()->format('Y-m-d H:i:s'))->toBe('2020-01-01 08:00:00');
    expect($report->updatedAt()->format('Y-m-d H:i:s'))->toBe('2020-01-02 09:00:00');
});

test('wraps malformed report dates with the Report date exception code', function (string $parameter): void {
    expect(fn () => new ReportEntity(
        ...['createdBy' => '550e8400-e29b-41d4-a716-446655440000', $parameter => 'not-a-date'],
    ))->toThrow(function (InvalidReportDateException $exception): void {
        expect($exception->getCode())->toBe(2020);
        expect($exception->getPrevious())->toBeInstanceOf(Exception::class);
    });
})->with(['createdAt', 'updatedAt']);

test('rejects inconsistent original and revision identities', function (array $revision): void {
    expect(fn () => new ReportEntity(
        ...array_replace([
            'createdBy' => '550e8400-e29b-41d4-a716-446655440000',
            'id' => '550e8400-e29b-41d4-a716-446655440010',
        ], $revision),
    ))->toThrow(function (InvalidReportRevisionException $exception): void {
        expect($exception->getCode())->toBe(2002);
    });
})->with([
    'negative revision' => [['revisionNumber' => -1]],
    'original with another root' => [['rootReportId' => '550e8400-e29b-41d4-a716-446655440011']],
    'original with parent' => [['parentReportId' => '550e8400-e29b-41d4-a716-446655440011']],
    'revision without parent' => [['revisionNumber' => 1, 'rootReportId' => '550e8400-e29b-41d4-a716-446655440011']],
    'revision with its own root' => [['revisionNumber' => 1, 'parentReportId' => '550e8400-e29b-41d4-a716-446655440011']],
    'revision with itself as parent' => [[
        'revisionNumber' => 1,
        'rootReportId' => '550e8400-e29b-41d4-a716-446655440011',
        'parentReportId' => '550e8400-e29b-41d4-a716-446655440010',
    ]],
    'first revision with parent different from root' => [[
        'revisionNumber' => 1,
        'rootReportId' => '550e8400-e29b-41d4-a716-446655440011',
        'parentReportId' => '550e8400-e29b-41d4-a716-446655440012',
    ]],
    'second revision directly from root' => [[
        'revisionNumber' => 2,
        'rootReportId' => '550e8400-e29b-41d4-a716-446655440011',
        'parentReportId' => '550e8400-e29b-41d4-a716-446655440011',
    ]],
]);

test('creates REV1 and REV2 with new identities and the same document family', function (): void {
    $original = generationReadyAggregateReport();
    $originalDocument = aggregateGeneratedDocument();
    $original->registerGeneratedDocument($originalDocument);
    $originalUpdatedAt = $original->updatedAt();

    $firstRevision = $original->createRevision('550e8400-e29b-41d4-a716-446655440001');
    $firstDocument = aggregateGeneratedDocument('SALVADOR_PM_1B_DELEGACIA_REV1.pdf');
    $firstRevision->registerGeneratedDocument($firstDocument);
    $firstUpdatedAt = $firstRevision->updatedAt();
    $secondRevision = $firstRevision->createRevision();

    expect($firstRevision->id()->value())->toBeUuid()->not->toBe($original->id()->value());
    expect($secondRevision->id()->value())->toBeUuid()->not->toBe($firstRevision->id()->value());
    expect($firstRevision->rootReportId()->value())->toBe($original->id()->value());
    expect($secondRevision->rootReportId()->value())->toBe($original->id()->value());
    expect($firstRevision->parentReportId()?->value())->toBe($original->id()->value());
    expect($secondRevision->parentReportId()?->value())->toBe($firstRevision->id()->value());
    expect($firstRevision->revisionNumber())->toBe(1);
    expect($secondRevision->revisionNumber())->toBe(2);
    expect($firstRevision->revisionLabel())->toBe('REV1');
    expect($secondRevision->revisionLabel())->toBe('REV2');
    expect($firstRevision->fileName())->toBe('SALVADOR_PM_1B_DELEGACIA_REV1.pdf');
    expect($secondRevision->isRevision())->toBeTrue();
    expect($secondRevision->createdBy()->value())->toBe('550e8400-e29b-41d4-a716-446655440001');
    expect($secondRevision->generatedDocument())->toBeNull();
    expect($secondRevision->status())->toBe(ReportStatusEnum::DRAFT);
    expect($secondRevision->fileName())->toBe('SALVADOR_PM_1B_DELEGACIA_REV2.pdf');

    foreach (['cover', 'generalInformation', 'location', 'infrastructure', 'preImplementation', 'photographicDocumentation', 'attachments', 'conclusion'] as $section) {
        expect($secondRevision->{$section}())->toBe($original->{$section}());
    }

    expect($original->generatedDocument())->toBe($originalDocument);
    expect($original->updatedAt())->toBe($originalUpdatedAt);
    expect($firstRevision->generatedDocument())->toBe($firstDocument);
    expect($firstRevision->updatedAt())->toBe($firstUpdatedAt);
    expect($secondRevision->createdAt())->not->toBe($original->createdAt());
    expect($secondRevision->createdAt()->format('Y-m-d H:i:s'))->not->toBe('2020-01-01 08:00:00');
});

test('keeps the original content when editing a new documentary revision', function (): void {
    $report = generationReadyAggregateReport();
    $report->registerGeneratedDocument(aggregateGeneratedDocument());
    $originalConclusion = $report->conclusion();
    $revision = $report->createRevision();
    $changedConclusion = new ReportConclusionValueObject('A vistoria complementar atualizou a conclusão.');

    $revision->changeConclusion($changedConclusion);

    expect($revision->conclusion())->toBe($changedConclusion);
    expect($report->conclusion())->toBe($originalConclusion);
    expect($report->isGenerated())->toBeTrue();
    expect($revision->isGenerated())->toBeFalse();
});

test('rejects every edit and PDF overwrite after a version has been generated', function (string $method, string $getter, Closure $replacement): void {
    $report = generationReadyAggregateReport();
    $document = aggregateGeneratedDocument();
    $report->registerGeneratedDocument($document);
    $previousValue = $report->{$getter}();
    $updatedAt = $report->updatedAt();

    expect(fn () => $report->{$method}($replacement()))->toThrow(function (ReportAlreadyGeneratedException $exception): void {
        expect($exception->getCode())->toBe(2003);
    });

    expect($report->{$getter}())->toBe($previousValue);
    expect($report->generatedDocument())->toBe($document);
    expect($report->status())->toBe(ReportStatusEnum::GENERATED);
    expect($report->updatedAt())->toBe($updatedAt);
})->with([
    'cover' => ['changeCover', 'cover', fn (): ReportCoverValueObject => new ReportCoverValueObject],
    'general information' => ['changeGeneralInformation', 'generalInformation', fn (): ReportGeneralInformationValueObject => new ReportGeneralInformationValueObject],
    'location' => ['changeLocation', 'location', fn (): ReportLocationValueObject => new ReportLocationValueObject],
    'infrastructure' => ['changeInfrastructure', 'infrastructure', fn (): ReportInfrastructureValueObject => new ReportInfrastructureValueObject],
    'pre-implementation' => ['changePreImplementation', 'preImplementation', fn (): ReportPreImplementationValueObject => new ReportPreImplementationValueObject],
    'photographic documentation' => ['changePhotographicDocumentation', 'photographicDocumentation', fn (): ReportPhotographicDocumentationValueObject => new ReportPhotographicDocumentationValueObject],
    'attachments' => ['changeAttachments', 'attachments', fn (): ReportAttachmentsValueObject => new ReportAttachmentsValueObject],
    'conclusion' => ['changeConclusion', 'conclusion', fn (): ReportConclusionValueObject => new ReportConclusionValueObject('Nova conclusão')],
    'generated document' => ['registerGeneratedDocument', 'generatedDocument', fn (): GeneratedReportValueObject => aggregateGeneratedDocument()],
]);

test('rejects generation and documentary revisions of incomplete drafts without freezing them', function (): void {
    $report = new ReportEntity(createdBy: '550e8400-e29b-41d4-a716-446655440000');
    $updatedAt = $report->updatedAt();

    expect(fn () => $report->validateForGeneration())->toThrow(function (IncompleteReportException $exception): void {
        expect($exception->getCode())->toBe(2005);
    });
    expect(fn () => $report->registerGeneratedDocument(aggregateGeneratedDocument()))->toThrow(IncompleteReportException::class);
    expect(fn () => $report->createRevision())->toThrow(function (ReportNotGeneratedException $exception): void {
        expect($exception->getCode())->toBe(2004);
    });

    expect($report->generatedDocument())->toBeNull();
    expect($report->status())->toBe(ReportStatusEnum::DRAFT);
    expect($report->updatedAt())->toBe($updatedAt);

    $conclusion = new ReportConclusionValueObject('Texto inicial ainda em elaboração.');
    $report->changeConclusion($conclusion);

    expect($report->conclusion())->toBe($conclusion);
});

test('requires each mandatory section before final generation while allowing its incomplete draft', function (string $section, Closure $replacement): void {
    $report = generationReadyAggregateReport([$section => $replacement()]);

    $report->validate();

    expect(fn () => $report->validateForGeneration())->toThrow(function (IncompleteReportException $exception): void {
        expect($exception->getCode())->toBe(2005);
    });
})->with([
    'cover selection' => ['cover', fn (): ReportCoverValueObject => new ReportCoverValueObject],
    'inspection information' => ['generalInformation', fn (): ReportGeneralInformationValueObject => new ReportGeneralInformationValueObject],
    'state location image' => ['location', fn (): ReportLocationValueObject => new ReportLocationValueObject(locationMap: aggregateReportImage(2))],
    'infrastructure responses' => ['infrastructure', fn (): ReportInfrastructureValueObject => new ReportInfrastructureValueObject(waterNetwork: ChecklistAnswerEnum::YES)],
    'terrain checklist responses' => ['attachments', fn (): ReportAttachmentsValueObject => new ReportAttachmentsValueObject(checklist: new ReportChecklistValueObject(seiConstructionRequest: ChecklistAnswerEnum::YES))],
    'conclusion section' => ['conclusion', fn (): null => null],
]);

test('rejects technical status inconsistent with document presence', function (ReportStatusEnum $status, ?GeneratedReportValueObject $document): void {
    expect(fn () => generationReadyAggregateReport(['status' => $status, 'generatedDocument' => $document]))
        ->toThrow(function (InvalidReportStatusException $exception): void {
            expect($exception->getCode())->toBe(2019);
        });
})->with([
    'generated without a document' => [ReportStatusEnum::GENERATED, null],
    'draft with a document' => [ReportStatusEnum::DRAFT, fn (): GeneratedReportValueObject => aggregateGeneratedDocument()],
]);

test('restores complete generated versions and derives their technical status from the document', function (): void {
    $document = aggregateGeneratedDocument();

    $report = generationReadyAggregateReport(['generatedDocument' => $document]);

    expect($report->isGenerated())->toBeTrue();
    expect($report->status())->toBe(ReportStatusEnum::GENERATED);
    expect($report->generatedDocument())->toBe($document);
});

test('rejects rehydrating a final document with incomplete content', function (): void {
    expect(fn () => generationReadyAggregateReport([
        'generatedDocument' => aggregateGeneratedDocument(),
        'infrastructure' => new ReportInfrastructureValueObject,
    ]))->toThrow(IncompleteReportException::class);
});

test('rejects a PDF name inconsistent with the current cover without freezing the report', function (): void {
    $report = generationReadyAggregateReport();
    $updatedAt = $report->updatedAt();

    expect(fn () => $report->registerGeneratedDocument(aggregateGeneratedDocument('WRONG_NAME.pdf')))
        ->toThrow(function (InvalidGeneratedReportException $exception): void {
            expect($exception->getCode())->toBe(2022);
        });

    expect($report->fileName())->toBe('SALVADOR_PM_1B_DELEGACIA.pdf');
    expect($report->generatedDocument())->toBeNull();
    expect($report->status())->toBe(ReportStatusEnum::DRAFT);
    expect($report->updatedAt())->toBe($updatedAt);

    $report->registerGeneratedDocument(aggregateGeneratedDocument());

    expect($report->status())->toBe(ReportStatusEnum::GENERATED);
});

test('counts location pre-implementation photography and attachments toward the global limit of twenty figures', function (): void {
    $photographs = array_map(fn (int $number): ReportImageEntity => aggregateReportImage($number), range(10, 25));
    $report = generationReadyAggregateReport([
        'location' => new ReportLocationValueObject(aggregateReportImage(1), aggregateReportImage(2)),
        'preImplementation' => new ReportPreImplementationValueObject(aggregateReportImage(3)),
        'photographicDocumentation' => new ReportPhotographicDocumentationValueObject($photographs),
        'attachments' => new ReportAttachmentsValueObject(
            checklist: generationReadyAggregateReport()->attachments()?->checklist(),
            others: [new ReportAttachmentEntity(ReportAttachmentTypeEnum::OTHER, aggregateReportFile(100), 'Planta auxiliar')],
        ),
    ]);

    $report->validateForGeneration();

    expect($report->photographicDocumentation()?->images())->toHaveCount(16);
    expect($report->attachments()?->files())->toHaveCount(1);
    expect(fn () => generationReadyAggregateReport([
        'location' => $report->location(),
        'preImplementation' => $report->preImplementation(),
        'attachments' => $report->attachments(),
        'photographicDocumentation' => new ReportPhotographicDocumentationValueObject([...$photographs, aggregateReportImage(26)]),
    ]))->toThrow(function (TooManyReportFiguresException $exception): void {
        expect($exception->getCode())->toBe(2015);
    });

    $previousPhotos = $report->photographicDocumentation();
    $updatedAt = $report->updatedAt();

    expect(fn () => $report->changePhotographicDocumentation(
        new ReportPhotographicDocumentationValueObject([...$photographs, aggregateReportImage(26)]),
    ))->toThrow(TooManyReportFiguresException::class);

    expect($report->photographicDocumentation())->toBe($previousPhotos);
    expect($report->updatedAt())->toBe($updatedAt);
});

test('rejects files duplicated across independent report sections and preserves the previous section', function (Closure $duplicateSection, string $method, string $getter): void {
    $report = generationReadyAggregateReport();
    $map = $report->location()?->municipalityInStateMap();
    $previousSection = $report->{$getter}();
    $updatedAt = $report->updatedAt();

    expect(fn () => $report->{$method}($duplicateSection($map)))->toThrow(function (DuplicateReportFileException $exception): void {
        expect($exception->getCode())->toBe(2014);
    });

    expect($report->{$getter}())->toBe($previousSection);
    expect($report->updatedAt())->toBe($updatedAt);
})->with([
    'location and pre-implementation same identity' => [
        fn (ReportImageEntity $map): ReportPreImplementationValueObject => new ReportPreImplementationValueObject($map),
        'changePreImplementation', 'preImplementation',
    ],
    'location and photograph same content with another identity' => [
        fn (ReportImageEntity $map): ReportPhotographicDocumentationValueObject => new ReportPhotographicDocumentationValueObject([
            new ReportImageEntity($map->file(), 1, id: '550e8400-e29b-41d4-a716-446655440020'),
        ]),
        'changePhotographicDocumentation', 'photographicDocumentation',
    ],
    'location and attachment same file' => [
        fn (ReportImageEntity $map): ReportAttachmentsValueObject => new ReportAttachmentsValueObject(others: [
            new ReportAttachmentEntity(ReportAttachmentTypeEnum::OTHER, $map->file(), 'Mapa duplicado'),
        ]),
        'changeAttachments', 'attachments',
    ],
    'same identity with another file' => [
        fn (ReportImageEntity $map): ReportPreImplementationValueObject => new ReportPreImplementationValueObject(
            new ReportImageEntity(aggregateReportFile(10), 1, id: $map->id()),
        ),
        'changePreImplementation', 'preImplementation',
    ],
]);

test('preserves an uploaded figure order after removal and attempted reintroduction', function (): void {
    $image = aggregateReportImage(10, 1);
    $report = generationReadyAggregateReport([
        'photographicDocumentation' => new ReportPhotographicDocumentationValueObject([$image]),
    ]);
    $emptyPhotos = new ReportPhotographicDocumentationValueObject;
    $report->changePhotographicDocumentation($emptyPhotos);
    $updatedAt = $report->updatedAt();
    $reordered = new ReportImageEntity($image->file(), 2, id: $image->id());

    expect(fn () => $report->changePhotographicDocumentation(new ReportPhotographicDocumentationValueObject([$reordered])))
        ->toThrow(function (InvalidReportImageOrderException $exception): void {
            expect($exception->getCode())->toBe(2016);
        });

    expect($report->photographicDocumentation())->toBe($emptyPhotos);
    expect($report->updatedAt())->toBe($updatedAt);
    expect($report->uploadedImages())->toContain($image);
});

test('rejects changing an uploaded figure file or order when moving it to another section', function (Closure $replacement): void {
    $image = aggregateReportImage(10, 1);
    $report = generationReadyAggregateReport([
        'photographicDocumentation' => new ReportPhotographicDocumentationValueObject([$image]),
    ]);
    $report->changePhotographicDocumentation(new ReportPhotographicDocumentationValueObject);
    $previousPreImplementation = $report->preImplementation();
    $updatedAt = $report->updatedAt();

    expect(fn () => $report->changePreImplementation(new ReportPreImplementationValueObject($replacement($image))))
        ->toThrow(InvalidReportImageOrderException::class);

    expect($report->preImplementation())->toBe($previousPreImplementation);
    expect($report->updatedAt())->toBe($updatedAt);
})->with([
    'same identity and altered order' => [fn (ReportImageEntity $image): ReportImageEntity => new ReportImageEntity($image->file(), 2, id: $image->id())],
    'same file under a new identity and altered order' => [fn (ReportImageEntity $image): ReportImageEntity => new ReportImageEntity($image->file(), 2)],
    'same identity and altered file' => [fn (ReportImageEntity $image): ReportImageEntity => new ReportImageEntity(aggregateReportFile(11), 1, id: $image->id())],
]);

test('allows caption changes while preserving the figure identity file and upload order', function (): void {
    $image = aggregateReportImage(10, 1);
    $report = generationReadyAggregateReport([
        'photographicDocumentation' => new ReportPhotographicDocumentationValueObject([$image]),
    ]);
    $replacement = $image->withCaption('Vista lateral do terreno.');

    $report->changePhotographicDocumentation(new ReportPhotographicDocumentationValueObject([$replacement]));

    expect($report->photographicDocumentation()?->images())->toBe([$replacement]);
    expect($replacement->caption())->toBe('Vista lateral do terreno.');
    expect($replacement->id()->value())->toBe($image->id()->value());
    expect($replacement->file())->toBe($image->file());
    expect($replacement->order())->toBe(1);
    expect($image->caption())->toBe('Figura do terreno 10');
});

test('rejects assigning a new identity to an existing upload even if its content and order are unchanged', function (): void {
    $image = aggregateReportImage(10, 1);
    $report = generationReadyAggregateReport([
        'photographicDocumentation' => new ReportPhotographicDocumentationValueObject([$image]),
    ]);
    $previousPhotos = $report->photographicDocumentation();
    $previousHistory = $report->uploadedImages();
    $updatedAt = $report->updatedAt();
    $replacement = new ReportImageEntity(
        $image->file(),
        1,
        id: '550e8400-e29b-41d4-a716-446655440099',
    );

    expect(fn () => $report->changePhotographicDocumentation(new ReportPhotographicDocumentationValueObject([$replacement])))
        ->toThrow(function (InvalidReportImageOrderException $exception): void {
            expect($exception->getCode())->toBe(2016);
        });

    expect($report->photographicDocumentation())->toBe($previousPhotos);
    expect($report->uploadedImages())->toBe($previousHistory);
    expect($report->updatedAt())->toBe($updatedAt);
});

test('allows restoring a removed upload under its original identity file and order', function (): void {
    $image = aggregateReportImage(10, 1);
    $report = generationReadyAggregateReport([
        'photographicDocumentation' => new ReportPhotographicDocumentationValueObject([$image]),
    ]);
    $report->changePhotographicDocumentation(new ReportPhotographicDocumentationValueObject);
    $previousHistory = $report->uploadedImages();

    $report->changePhotographicDocumentation(new ReportPhotographicDocumentationValueObject([$image]));

    expect($report->photographicDocumentation()?->images())->toBe([$image]);
    expect($report->uploadedImages())->toBe($previousHistory);
});

test('carries removed upload history into a documentary revision to preserve upload order', function (): void {
    $image = aggregateReportImage(10, 1);
    $original = generationReadyAggregateReport([
        'photographicDocumentation' => new ReportPhotographicDocumentationValueObject([$image]),
    ]);
    $original->changePhotographicDocumentation(new ReportPhotographicDocumentationValueObject);
    $original->registerGeneratedDocument(aggregateGeneratedDocument());
    $revision = $original->createRevision();
    $replacement = new ReportImageEntity($image->file(), 2, id: $image->id());

    expect(fn () => $revision->changePhotographicDocumentation(new ReportPhotographicDocumentationValueObject([$replacement])))
        ->toThrow(InvalidReportImageOrderException::class);

    expect($revision->uploadedImages())->toContain($image);
    expect($revision->photographicDocumentation()?->images())->toBe([]);
    expect($original->isGenerated())->toBeTrue();
});

test('restores removed upload history so rehydration cannot reorder a previously uploaded image', function (): void {
    $image = aggregateReportImage(10, 1);
    $report = generationReadyAggregateReport(['uploadedImages' => [$image]]);
    $replacement = new ReportImageEntity($image->file(), 2, id: $image->id());

    expect(fn () => $report->changePhotographicDocumentation(new ReportPhotographicDocumentationValueObject([$replacement])))
        ->toThrow(InvalidReportImageOrderException::class);
});
