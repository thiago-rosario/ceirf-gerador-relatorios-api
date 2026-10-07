<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\CreateReportRevisionInputDTO;
use src\Modules\Report\Application\Exception\ReportNotFoundException;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Application\Interfaces\Service\ReportFinderServiceInterface;
use src\Modules\Report\Application\Service\ReportDataMapperService;
use src\Modules\Report\Application\Usecase\CreateReportRevisionUsecase;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Enum\ReportStatusEnum;
use src\Modules\Report\Domain\Exception\InvalidReportIdException;
use src\Modules\Report\Domain\Exception\InvalidReportRevisionException;
use src\Modules\Report\Domain\Exception\ReportNotGeneratedException;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;
use Tests\Fixtures\ReportApplicationFixtures;

afterEach(function (): void {
    Mockery::close();
});

test('creates an editable revision from a generated report and maps the saved revision', function (?string $createdBy): void {
    $report = ReportApplicationFixtures::generatedReport();
    $original = clone $report;
    $input = new CreateReportRevisionInputDTO(id: $report->id()->value(), createdBy: $createdBy);
    $savedRevision = $report->createRevision($createdBy);
    $reportData = (new ReportDataMapperService)->map($savedRevision);
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('insert')->once()->with(Mockery::on(
        fn (ReportEntity $candidate): bool => $candidate->id()->value() !== $report->id()->value()
            && $candidate->rootReportId()->value() === $report->rootReportId()->value()
            && $candidate->parentReportId()?->value() === $report->id()->value()
            && $candidate->revisionNumber() === 1
            && $candidate->createdBy()->value() === ($createdBy ?? $report->createdBy()->value())
            && $candidate->status() === ReportStatusEnum::DRAFT
            && ! $candidate->isGenerated()
            && $candidate->cover() === $report->cover()
            && $candidate->generalInformation() === $report->generalInformation()
            && $candidate->location() === $report->location()
            && $candidate->infrastructure() === $report->infrastructure()
            && $candidate->preImplementation() === $report->preImplementation()
            && $candidate->photographicDocumentation() === $report->photographicDocumentation()
            && $candidate->attachments() === $report->attachments()
            && $candidate->conclusion() === $report->conclusion()
            && $candidate->uploadedImages() === $report->uploadedImages(),
    ))->andReturn($savedRevision);
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldReceive('map')->once()->with($savedRevision)->andReturn($reportData);
    $usecase = new CreateReportRevisionUsecase($repository, $finder, $mapper);

    $output = $usecase($input);

    expect($output->report)->toBe($reportData);
    expect($report)->toEqual($original);
})->with([
    'original author retained' => [null],
    'new revision author supplied' => ['550e8400-e29b-41d4-a716-446655440099'],
]);

test('does not persist a revision when its source is not found', function (): void {
    $input = new CreateReportRevisionInputDTO('550e8400-e29b-41d4-a716-446655440010');
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andThrow(new ReportNotFoundException);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('insert');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new CreateReportRevisionUsecase($repository, $finder, $mapper);

    expect(fn () => $usecase($input))->toThrow(ReportNotFoundException::class);
});

test('rejects a draft source without persisting a revision', function (): void {
    $report = ReportApplicationFixtures::completeReport();
    $input = new CreateReportRevisionInputDTO($report->id()->value());
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('insert');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new CreateReportRevisionUsecase($repository, $finder, $mapper);

    expect(fn () => $usecase($input))->toThrow(ReportNotGeneratedException::class);
});

test('propagates invalid revision authors without persisting a revision', function (): void {
    $report = ReportApplicationFixtures::generatedReport();
    $input = new CreateReportRevisionInputDTO(id: $report->id()->value(), createdBy: 'invalid-author-id');
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('insert');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new CreateReportRevisionUsecase($repository, $finder, $mapper);

    expect(fn () => $usecase($input))->toThrow(InvalidReportIdException::class);
});

test('propagates the domain revision limit without persisting a revision', function (): void {
    $report = ReportApplicationFixtures::completeReport([
        'rootReportId' => '550e8400-e29b-41d4-a716-446655440020',
        'parentReportId' => '550e8400-e29b-41d4-a716-446655440030',
        'revisionNumber' => PHP_INT_MAX,
    ]);
    $report->registerGeneratedDocument(ReportApplicationFixtures::document($report->fileName()));
    $input = new CreateReportRevisionInputDTO($report->id()->value());
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('insert');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new CreateReportRevisionUsecase($repository, $finder, $mapper);

    expect(fn () => $usecase($input))->toThrow(InvalidReportRevisionException::class);
});

test('propagates persistence failures while preserving the generated source', function (): void {
    $report = ReportApplicationFixtures::generatedReport();
    $original = clone $report;
    $input = new CreateReportRevisionInputDTO($report->id()->value());
    $exception = new RuntimeException('Falha ao salvar a revisão.');
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('insert')->once()->with(Mockery::type(ReportEntity::class))->andThrow($exception);
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new CreateReportRevisionUsecase($repository, $finder, $mapper);

    expect(fn () => $usecase($input))->toThrow($exception);

    expect($report)->toEqual($original);
});
