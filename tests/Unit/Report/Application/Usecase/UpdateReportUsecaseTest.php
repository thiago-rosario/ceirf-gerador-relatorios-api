<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\UpdateReportInputDTO;
use src\Modules\Report\Application\Exception\ReportNotFoundException;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Application\Interfaces\Service\ReportFinderServiceInterface;
use src\Modules\Report\Application\Service\ReportDataMapperService;
use src\Modules\Report\Application\Usecase\UpdateReportUsecase;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Exception\DuplicateReportFileException;
use src\Modules\Report\Domain\Exception\ReportAlreadyGeneratedException;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;
use src\Modules\Report\Domain\ValueObject\ReportAttachmentsValueObject;
use src\Modules\Report\Domain\ValueObject\ReportConclusionValueObject;
use src\Modules\Report\Domain\ValueObject\ReportCoverValueObject;
use src\Modules\Report\Domain\ValueObject\ReportGeneralInformationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportInfrastructureValueObject;
use src\Modules\Report\Domain\ValueObject\ReportLocationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportPhotographicDocumentationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportPreImplementationValueObject;
use Tests\Fixtures\ReportApplicationFixtures;

afterEach(function (): void {
    Mockery::close();
});

test('updates all supplied sections on a copy and returns the saved report data', function (): void {
    $report = ReportApplicationFixtures::completeReport();
    $original = clone $report;
    $input = new UpdateReportInputDTO(
        id: $report->id()->value(),
        cover: new ReportCoverValueObject(typology: 'DEAM'),
        generalInformation: new ReportGeneralInformationValueObject(collaborators: 'João Santos'),
        location: new ReportLocationValueObject,
        infrastructure: new ReportInfrastructureValueObject,
        preImplementation: new ReportPreImplementationValueObject,
        photographicDocumentation: new ReportPhotographicDocumentationValueObject,
        attachments: new ReportAttachmentsValueObject,
        conclusion: new ReportConclusionValueObject('Parecer atualizado.'),
    );
    $savedReport = new ReportEntity(
        createdBy: $report->createdBy(),
        id: '550e8400-e29b-41d4-a716-446655440099',
        createdAt: '2020-02-01 08:00:00',
    );
    $reportData = (new ReportDataMapperService)->map($savedReport);
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (ReportEntity $candidate): bool => $candidate !== $report
            && $candidate->id()->value() === $input->id
            && $candidate->cover() === $input->cover
            && $candidate->generalInformation() === $input->generalInformation
            && $candidate->location() === $input->location
            && $candidate->infrastructure() === $input->infrastructure
            && $candidate->preImplementation() === $input->preImplementation
            && $candidate->photographicDocumentation() === $input->photographicDocumentation
            && $candidate->attachments() === $input->attachments
            && $candidate->conclusion() === $input->conclusion
            && $candidate->uploadedImages() === $report->uploadedImages(),
    ))->andReturn($savedReport);
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldReceive('map')->once()->with($savedReport)->andReturn($reportData);
    $usecase = new UpdateReportUsecase($repository, $finder, $mapper);

    $output = $usecase($input);

    expect($output->report)->toBe($reportData);
    expect($report)->toEqual($original);
});

test('preserves omitted sections when updating an editable revision', function (): void {
    $report = ReportApplicationFixtures::generatedReport()->createRevision();
    $input = new UpdateReportInputDTO(
        id: $report->id()->value(),
        conclusion: new ReportConclusionValueObject('Parecer revisado.'),
    );
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (ReportEntity $candidate): bool => $candidate->cover() === $report->cover()
            && $candidate->generalInformation() === $report->generalInformation()
            && $candidate->location() === $report->location()
            && $candidate->infrastructure() === $report->infrastructure()
            && $candidate->preImplementation() === $report->preImplementation()
            && $candidate->photographicDocumentation() === $report->photographicDocumentation()
            && $candidate->attachments() === $report->attachments()
            && $candidate->conclusion() === $input->conclusion,
    ))->andReturnUsing(fn (ReportEntity $candidate): ReportEntity => $candidate);
    $usecase = new UpdateReportUsecase($repository, $finder, new ReportDataMapperService);

    $output = $usecase($input);

    expect($output->report->revisionNumber)->toBe(1);
    expect($output->report->conclusion?->content)->toBe('Parecer revisado.');
});

test('moves a photographic image into location in the same update while retaining its upload history', function (): void {
    $image = ReportApplicationFixtures::image(2);
    $report = ReportApplicationFixtures::completeReport([
        'photographicDocumentation' => new ReportPhotographicDocumentationValueObject([$image]),
    ]);
    $original = clone $report;
    $input = new UpdateReportInputDTO(
        id: $report->id()->value(),
        location: new ReportLocationValueObject(
            locationMap: $image,
            municipalityInStateMap: $report->location()->municipalityInStateMap(),
        ),
        photographicDocumentation: new ReportPhotographicDocumentationValueObject,
    );
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (ReportEntity $candidate): bool => $candidate->location() === $input->location
            && $candidate->photographicDocumentation() === $input->photographicDocumentation
            && $candidate->preImplementation() === $report->preImplementation()
            && $candidate->attachments() === $report->attachments()
            && $candidate->uploadedImages() === $report->uploadedImages(),
    ))->andReturnUsing(fn (ReportEntity $candidate): ReportEntity => $candidate);
    $usecase = new UpdateReportUsecase($repository, $finder, new ReportDataMapperService);

    $output = $usecase($input);

    expect($output->report->location?->locationMap?->id)->toBe('550e8400-e29b-41d4-a716-000000000002');
    expect($output->report->photographicDocumentation?->images)->toBe([]);
    expect($report)->toEqual($original);
});

test('swaps images between location and photographic documentation in the same update', function (): void {
    $locationImage = ReportApplicationFixtures::image(2);
    $photographicImage = ReportApplicationFixtures::image(3);
    $municipalityMap = ReportApplicationFixtures::image(1);
    $report = ReportApplicationFixtures::completeReport([
        'location' => new ReportLocationValueObject($locationImage, $municipalityMap),
        'photographicDocumentation' => new ReportPhotographicDocumentationValueObject([$photographicImage]),
    ]);
    $original = clone $report;
    $input = new UpdateReportInputDTO(
        id: $report->id()->value(),
        location: new ReportLocationValueObject($photographicImage, $municipalityMap),
        photographicDocumentation: new ReportPhotographicDocumentationValueObject([$locationImage]),
    );
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (ReportEntity $candidate): bool => $candidate->location() === $input->location
            && $candidate->photographicDocumentation() === $input->photographicDocumentation
            && $candidate->uploadedImages() === $report->uploadedImages(),
    ))->andReturnUsing(fn (ReportEntity $candidate): ReportEntity => $candidate);
    $usecase = new UpdateReportUsecase($repository, $finder, new ReportDataMapperService);

    $output = $usecase($input);

    expect($output->report->location?->locationMap?->id)->toBe('550e8400-e29b-41d4-a716-000000000003');
    expect($output->report->photographicDocumentation?->images[0]->id)->toBe('550e8400-e29b-41d4-a716-000000000002');
    expect($report)->toEqual($original);
});

test('returns the report unchanged without persisting when no section is supplied', function (Closure $createReport): void {
    $report = $createReport();
    $input = new UpdateReportInputDTO($report->id()->value());
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('update');
    $usecase = new UpdateReportUsecase($repository, $finder, new ReportDataMapperService);

    $output = $usecase($input);

    expect($output->report->updatedAt)->toBe($report->updatedAt());
})->with([
    'draft report' => [fn (): ReportEntity => ReportApplicationFixtures::completeReport()],
    'generated report' => [fn (): ReportEntity => ReportApplicationFixtures::generatedReport()],
]);

test('rejects changing generated reports without persisting', function (): void {
    $report = ReportApplicationFixtures::generatedReport();
    $input = new UpdateReportInputDTO(
        id: $report->id()->value(),
        conclusion: new ReportConclusionValueObject('Parecer substituído.'),
    );
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('update');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new UpdateReportUsecase($repository, $finder, $mapper);

    expect(fn () => $usecase($input))->toThrow(ReportAlreadyGeneratedException::class);
});

test('does not persist when the report is not found', function (): void {
    $input = new UpdateReportInputDTO('550e8400-e29b-41d4-a716-446655440010');
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andThrow(new ReportNotFoundException);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('update');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new UpdateReportUsecase($repository, $finder, $mapper);

    expect(fn () => $usecase($input))->toThrow(ReportNotFoundException::class);
});

test('preserves the original report when a later section violates aggregate rules', function (): void {
    $report = ReportApplicationFixtures::completeReport();
    $original = clone $report;
    $input = new UpdateReportInputDTO(
        id: $report->id()->value(),
        cover: new ReportCoverValueObject(typology: 'DEAM'),
        photographicDocumentation: new ReportPhotographicDocumentationValueObject([
            $report->location()->municipalityInStateMap(),
        ]),
    );
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('update');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new UpdateReportUsecase($repository, $finder, $mapper);

    expect(fn () => $usecase($input))->toThrow(DuplicateReportFileException::class);

    expect($report)->toEqual($original);
});

test('propagates persistence failures and preserves the original report', function (): void {
    $report = ReportApplicationFixtures::completeReport();
    $original = clone $report;
    $input = new UpdateReportInputDTO(
        id: $report->id()->value(),
        conclusion: new ReportConclusionValueObject('Parecer atualizado.'),
    );
    $exception = new RuntimeException('Falha ao salvar o relatório.');
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('update')->once()->with(Mockery::type(ReportEntity::class))->andThrow($exception);
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new UpdateReportUsecase($repository, $finder, $mapper);

    expect(fn () => $usecase($input))->toThrow($exception);

    expect($report)->toEqual($original);
});
