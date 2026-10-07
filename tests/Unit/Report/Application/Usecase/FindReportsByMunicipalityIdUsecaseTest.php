<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\FindReportsByMunicipalityIdInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;
use src\Modules\Report\Application\Interfaces\Adapter\ReportQueryResultAdapterInterface;
use src\Modules\Report\Application\Service\ReportDataMapperService;
use src\Modules\Report\Application\Usecase\FindReportsByMunicipalityIdUsecase;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('returns typed reports adapted from the municipality query result', function (): void {
    $source = ['opaque-result' => ['repository-specific-value']];
    $report = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        id: '550e8400-e29b-41d4-a716-446655440001',
    );
    $reports = [(new ReportDataMapperService)->map($report)];
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findReportByMunicipalityId')->once()->with('2927408')->andReturn($source);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldReceive('toReports')->once()->with($source)->andReturn($reports);
    $usecase = new FindReportsByMunicipalityIdUsecase($repository, $adapter);

    $output = $usecase(new FindReportsByMunicipalityIdInputDTO('2927408'));

    expect($output)->toBeInstanceOf(ListReportsOutputDTO::class);
    expect($output->reports)->toBe($reports);
});

test('returns no reports without adapting a null municipality query result', function (): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findReportByMunicipalityId')->once()->with('2927408')->andReturnNull();
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldNotReceive('toReports');
    $usecase = new FindReportsByMunicipalityIdUsecase($repository, $adapter);

    $output = $usecase(new FindReportsByMunicipalityIdInputDTO('2927408'));

    expect($output->reports)->toBe([]);
});

test('adapts an empty municipality query result', function (): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findReportByMunicipalityId')->once()->with('2927408')->andReturn([]);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldReceive('toReports')->once()->with([])->andReturn([]);
    $usecase = new FindReportsByMunicipalityIdUsecase($repository, $adapter);

    $output = $usecase(new FindReportsByMunicipalityIdInputDTO('2927408'));

    expect($output->reports)->toBe([]);
});

test('propagates a municipality query failure before adapting results', function (): void {
    $exception = new RuntimeException('Falha ao consultar os relatórios.');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findReportByMunicipalityId')->once()->with('2927408')->andThrow($exception);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldNotReceive('toReports');
    $usecase = new FindReportsByMunicipalityIdUsecase($repository, $adapter);

    expect(fn () => $usecase(new FindReportsByMunicipalityIdInputDTO('2927408')))->toThrow($exception);
});

test('propagates a failure adapting the municipality query result', function (): void {
    $source = ['opaque-result' => ['repository-specific-value']];
    $exception = new RuntimeException('Falha ao adaptar os relatórios.');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findReportByMunicipalityId')->once()->with('2927408')->andReturn($source);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldReceive('toReports')->once()->with($source)->andThrow($exception);
    $usecase = new FindReportsByMunicipalityIdUsecase($repository, $adapter);

    expect(fn () => $usecase(new FindReportsByMunicipalityIdInputDTO('2927408')))->toThrow($exception);
});
