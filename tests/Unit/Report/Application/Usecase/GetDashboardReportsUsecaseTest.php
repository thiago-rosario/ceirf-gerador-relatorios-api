<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\GetDashboardReportsOutputDTO;
use src\Modules\Report\Application\Interfaces\Adapter\ReportQueryResultAdapterInterface;
use src\Modules\Report\Application\Service\ReportDataMapperService;
use src\Modules\Report\Application\Usecase\GetDashboardReportsUsecase;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('returns the repository total together with adapted dashboard reports', function (): void {
    $source = ['opaque-result' => ['repository-specific-value']];
    $report = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        id: '550e8400-e29b-41d4-a716-446655440001',
    );
    $reports = [(new ReportDataMapperService)->map($report)];
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('countAllReports')->once()->withNoArgs()->andReturn(42);
    $repository->shouldReceive('getDashboardReports')->once()->withNoArgs()->andReturn($source);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldReceive('toReports')->once()->with($source)->andReturn($reports);
    $usecase = new GetDashboardReportsUsecase($repository, $adapter);

    $output = $usecase();

    expect($output)->toBeInstanceOf(GetDashboardReportsOutputDTO::class);
    expect($output->totalReports)->toBe(42);
    expect($output->reports)->toBe($reports);
});

test('preserves the total without adapting missing dashboard reports', function (): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('countAllReports')->once()->withNoArgs()->andReturn(42);
    $repository->shouldReceive('getDashboardReports')->once()->withNoArgs()->andReturnNull();
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldNotReceive('toReports');
    $usecase = new GetDashboardReportsUsecase($repository, $adapter);

    $output = $usecase();

    expect($output->totalReports)->toBe(42);
    expect($output->reports)->toBe([]);
});

test('returns an empty dashboard when the repository has no reports', function (): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('countAllReports')->once()->withNoArgs()->andReturn(0);
    $repository->shouldReceive('getDashboardReports')->once()->withNoArgs()->andReturn([]);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldReceive('toReports')->once()->with([])->andReturn([]);
    $usecase = new GetDashboardReportsUsecase($repository, $adapter);

    $output = $usecase();

    expect($output->totalReports)->toBe(0);
    expect($output->reports)->toBe([]);
});

test('propagates a dashboard query failure before adapting results', function (): void {
    $exception = new RuntimeException('Falha ao consultar o dashboard.');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('countAllReports')->once()->withNoArgs()->andReturn(42);
    $repository->shouldReceive('getDashboardReports')->once()->withNoArgs()->andThrow($exception);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldNotReceive('toReports');
    $usecase = new GetDashboardReportsUsecase($repository, $adapter);

    expect(fn () => $usecase())->toThrow($exception);
});

test('propagates a failure adapting dashboard reports', function (): void {
    $source = ['opaque-result' => ['repository-specific-value']];
    $exception = new RuntimeException('Falha ao adaptar o dashboard.');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('countAllReports')->once()->withNoArgs()->andReturn(42);
    $repository->shouldReceive('getDashboardReports')->once()->withNoArgs()->andReturn($source);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldReceive('toReports')->once()->with($source)->andThrow($exception);
    $usecase = new GetDashboardReportsUsecase($repository, $adapter);

    expect(fn () => $usecase())->toThrow($exception);
});
