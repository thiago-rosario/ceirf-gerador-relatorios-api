<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\FindLatestReportInputDTO;
use src\Modules\Report\Application\DTO\FindLatestReportOutputDTO;
use src\Modules\Report\Application\Exception\ReportNotFoundException;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Application\Service\ReportDataMapperService;
use src\Modules\Report\Application\Service\ReportFinderService;
use src\Modules\Report\Application\Usecase\FindLatestReportUsecase;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('resolves the latest version using the root of the requested revision', function (): void {
    $rootReportId = '550e8400-e29b-41d4-a716-446655440001';
    $requestedReportId = '550e8400-e29b-41d4-a716-446655440002';
    $requestedRevision = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        id: $requestedReportId,
        rootReportId: $rootReportId,
        parentReportId: $rootReportId,
        revisionNumber: 1,
    );
    $latestRevision = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        id: '550e8400-e29b-41d4-a716-446655440003',
        rootReportId: $rootReportId,
        parentReportId: $requestedReportId,
        revisionNumber: 2,
    );
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with($requestedReportId)->andReturn($requestedRevision);
    $repository->shouldReceive('findLatestByRootReportId')->once()->with($rootReportId)->andReturn($latestRevision);
    $repository->shouldNotReceive('findLatestReportById');
    $usecase = new FindLatestReportUsecase($repository, new ReportFinderService($repository), new ReportDataMapperService);

    $output = $usecase(new FindLatestReportInputDTO($requestedReportId));

    expect($output)->toBeInstanceOf(FindLatestReportOutputDTO::class);
    expect($output->report->id)->toBe('550e8400-e29b-41d4-a716-446655440003');
    expect($output->report->revisionNumber)->toBe(2);
});

test('reports a missing requested version before querying its family', function (): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('unknown-report')->andReturnNull();
    $repository->shouldNotReceive('findLatestByRootReportId');
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new FindLatestReportUsecase($repository, new ReportFinderService($repository), $mapper);

    expect(fn () => $usecase(new FindLatestReportInputDTO('unknown-report')))->toThrow(ReportNotFoundException::class);
});

test('reports a missing latest version without mapping the requested report', function (): void {
    $report = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        id: '550e8400-e29b-41d4-a716-446655440001',
    );
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440001')->andReturn($report);
    $repository->shouldReceive('findLatestByRootReportId')->once()->with('550e8400-e29b-41d4-a716-446655440001')->andReturnNull();
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new FindLatestReportUsecase($repository, new ReportFinderService($repository), $mapper);

    expect(fn () => $usecase(new FindLatestReportInputDTO('550e8400-e29b-41d4-a716-446655440001')))->toThrow(ReportNotFoundException::class);
});

test('propagates a latest version lookup failure', function (): void {
    $report = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        id: '550e8400-e29b-41d4-a716-446655440001',
    );
    $exception = new RuntimeException('Falha ao consultar a última versão.');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440001')->andReturn($report);
    $repository->shouldReceive('findLatestByRootReportId')->once()->with('550e8400-e29b-41d4-a716-446655440001')->andThrow($exception);
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new FindLatestReportUsecase($repository, new ReportFinderService($repository), $mapper);

    expect(fn () => $usecase(new FindLatestReportInputDTO('550e8400-e29b-41d4-a716-446655440001')))->toThrow($exception);
});
