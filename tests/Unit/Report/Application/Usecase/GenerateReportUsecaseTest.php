<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\GenerateReportInputDTO;
use src\Modules\Report\Application\Exception\ReportNotFoundException;
use src\Modules\Report\Application\Interfaces\Adapter\ReportGeneratorAdapterInterface;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Application\Interfaces\Service\ReportFinderServiceInterface;
use src\Modules\Report\Application\Service\ReportDataMapperService;
use src\Modules\Report\Application\Usecase\GenerateReportUsecase;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Enum\ReportStatusEnum;
use src\Modules\Report\Domain\Exception\IncompleteReportException;
use src\Modules\Report\Domain\Exception\InvalidGeneratedReportException;
use src\Modules\Report\Domain\Exception\ReportAlreadyGeneratedException;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;
use src\Modules\Report\Domain\ValueObject\GeneratedReportValueObject;
use src\Modules\Report\Domain\ValueObject\ReportConclusionValueObject;
use Tests\Fixtures\ReportApplicationFixtures;

afterEach(function (): void {
    Mockery::close();
});

test('generates the complete draft registers its document and maps the persisted report', function (): void {
    $report = ReportApplicationFixtures::completeReport();
    $original = clone $report;
    $input = new GenerateReportInputDTO($report->id()->value());
    $document = ReportApplicationFixtures::document($report->fileName());
    $savedReport = ReportApplicationFixtures::generatedReport();
    $reportData = (new ReportDataMapperService)->map($savedReport);
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $generator = Mockery::mock(ReportGeneratorAdapterInterface::class);
    $generator->shouldReceive('generate')->once()->with(Mockery::on(
        fn (ReportEntity $candidate): bool => $candidate !== $report && $candidate == $report,
    ))->andReturn($document);
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (ReportEntity $candidate): bool => $candidate !== $report
            && $candidate->id()->value() === $input->id
            && $candidate->generatedDocument() === $document
            && $candidate->status() === ReportStatusEnum::GENERATED
            && $candidate->isGenerated(),
    ))->andReturn($savedReport);
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldReceive('map')->once()->with($savedReport)->andReturn($reportData);
    $usecase = new GenerateReportUsecase($repository, $finder, $mapper, $generator);

    $output = $usecase($input);

    expect($output->report)->toBe($reportData);
    expect($report)->toEqual($original);
});

test('keeps generator mutations separate from the report persisted by the application', function (): void {
    $report = ReportApplicationFixtures::completeReport();
    $original = clone $report;
    $input = new GenerateReportInputDTO($report->id()->value());
    $document = ReportApplicationFixtures::document($report->fileName());
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $generator = Mockery::mock(ReportGeneratorAdapterInterface::class);
    $generator->shouldReceive('generate')->once()->with(Mockery::type(ReportEntity::class))
        ->andReturnUsing(function (ReportEntity $candidate) use ($document): GeneratedReportValueObject {
            $candidate->changeConclusion(new ReportConclusionValueObject('Conteúdo local do adaptador.'));
            $candidate->registerGeneratedDocument($document);

            return $document;
        });
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (ReportEntity $candidate): bool => $candidate->conclusion() === $report->conclusion()
            && $candidate->generatedDocument() === $document,
    ))->andReturnUsing(fn (ReportEntity $candidate): ReportEntity => $candidate);
    $usecase = new GenerateReportUsecase($repository, $finder, new ReportDataMapperService, $generator);

    $output = $usecase($input);

    expect($output->report->conclusion?->content)->toBe('O terreno apresenta condições adequadas à implantação.');
    expect($report)->toEqual($original);
});

test('does not generate or persist when the report is not found', function (): void {
    $input = new GenerateReportInputDTO('550e8400-e29b-41d4-a716-446655440010');
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andThrow(new ReportNotFoundException);
    $generator = Mockery::mock(ReportGeneratorAdapterInterface::class);
    $generator->shouldNotReceive('generate');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('update');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new GenerateReportUsecase($repository, $finder, $mapper, $generator);

    expect(fn () => $usecase($input))->toThrow(ReportNotFoundException::class);
});

test('rejects generated reports before invoking the generator or persistence', function (): void {
    $report = ReportApplicationFixtures::generatedReport();
    $input = new GenerateReportInputDTO($report->id()->value());
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $generator = Mockery::mock(ReportGeneratorAdapterInterface::class);
    $generator->shouldNotReceive('generate');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('update');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new GenerateReportUsecase($repository, $finder, $mapper, $generator);

    expect(fn () => $usecase($input))->toThrow(ReportAlreadyGeneratedException::class);
});

test('rejects incomplete drafts before invoking the generator or persistence', function (): void {
    $report = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        id: '550e8400-e29b-41d4-a716-446655440010',
    );
    $input = new GenerateReportInputDTO($report->id()->value());
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $generator = Mockery::mock(ReportGeneratorAdapterInterface::class);
    $generator->shouldNotReceive('generate');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('update');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new GenerateReportUsecase($repository, $finder, $mapper, $generator);

    expect(fn () => $usecase($input))->toThrow(IncompleteReportException::class);
});

test('rejects a generated document with a mismatched file name without persisting', function (): void {
    $report = ReportApplicationFixtures::completeReport();
    $original = clone $report;
    $input = new GenerateReportInputDTO($report->id()->value());
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $generator = Mockery::mock(ReportGeneratorAdapterInterface::class);
    $generator->shouldReceive('generate')->once()->with(Mockery::type(ReportEntity::class))
        ->andReturn(ReportApplicationFixtures::document('outro-relatorio.pdf'));
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('update');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new GenerateReportUsecase($repository, $finder, $mapper, $generator);

    expect(fn () => $usecase($input))->toThrow(InvalidGeneratedReportException::class);

    expect($report)->toEqual($original);
});

test('propagates generator failures without persisting or mutating the source', function (): void {
    $report = ReportApplicationFixtures::completeReport();
    $original = clone $report;
    $input = new GenerateReportInputDTO($report->id()->value());
    $exception = new RuntimeException('Falha ao gerar o documento.');
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $generator = Mockery::mock(ReportGeneratorAdapterInterface::class);
    $generator->shouldReceive('generate')->once()->with(Mockery::type(ReportEntity::class))
        ->andReturnUsing(function (ReportEntity $candidate) use ($exception): never {
            $candidate->changeConclusion(new ReportConclusionValueObject('Conteúdo local do adaptador.'));

            throw $exception;
        });
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldNotReceive('update');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new GenerateReportUsecase($repository, $finder, $mapper, $generator);

    expect(fn () => $usecase($input))->toThrow($exception);

    expect($report)->toEqual($original);
});

test('propagates persistence failures while leaving the source editable', function (): void {
    $report = ReportApplicationFixtures::completeReport();
    $original = clone $report;
    $input = new GenerateReportInputDTO($report->id()->value());
    $exception = new RuntimeException('Falha ao salvar o documento gerado.');
    $finder = Mockery::mock(ReportFinderServiceInterface::class);
    $finder->shouldReceive('findById')->once()->with($input->id)->andReturn($report);
    $generator = Mockery::mock(ReportGeneratorAdapterInterface::class);
    $generator->shouldReceive('generate')->once()->with(Mockery::type(ReportEntity::class))
        ->andReturn(ReportApplicationFixtures::document($report->fileName()));
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('update')->once()->with(Mockery::on(
        fn (ReportEntity $candidate): bool => $candidate->isGenerated(),
    ))->andThrow($exception);
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new GenerateReportUsecase($repository, $finder, $mapper, $generator);

    expect(fn () => $usecase($input))->toThrow($exception);

    expect($report)->toEqual($original);
});
