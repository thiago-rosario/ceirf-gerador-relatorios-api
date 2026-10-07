<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\FindReportsByUserIdInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;
use src\Modules\Report\Application\Interfaces\Adapter\ReportQueryResultAdapterInterface;
use src\Modules\Report\Application\Service\ReportDataMapperService;
use src\Modules\Report\Application\Usecase\FindReportsByUserIdUsecase;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('returns typed reports adapted from the user query result', function (): void {
    $source = ['opaque-result' => ['repository-specific-value']];
    $report = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        id: '550e8400-e29b-41d4-a716-446655440001',
    );
    $reports = [(new ReportDataMapperService)->map($report)];
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('getReportByUserId')->once()->with('user-id')->andReturn($source);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldReceive('toReports')->once()->with($source)->andReturn($reports);
    $usecase = new FindReportsByUserIdUsecase($repository, $adapter);

    $output = $usecase(new FindReportsByUserIdInputDTO('user-id'));

    expect($output)->toBeInstanceOf(ListReportsOutputDTO::class);
    expect($output->reports)->toBe($reports);
});

test('returns no reports without adapting a null user query result', function (): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('getReportByUserId')->once()->with('user-id')->andReturnNull();
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldNotReceive('toReports');
    $usecase = new FindReportsByUserIdUsecase($repository, $adapter);

    $output = $usecase(new FindReportsByUserIdInputDTO('user-id'));

    expect($output->reports)->toBe([]);
});

test('adapts an empty user query result', function (): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('getReportByUserId')->once()->with('user-id')->andReturn([]);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldReceive('toReports')->once()->with([])->andReturn([]);
    $usecase = new FindReportsByUserIdUsecase($repository, $adapter);

    $output = $usecase(new FindReportsByUserIdInputDTO('user-id'));

    expect($output->reports)->toBe([]);
});

test('propagates a user query failure before adapting results', function (): void {
    $exception = new RuntimeException('Falha ao consultar os relatórios.');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('getReportByUserId')->once()->with('user-id')->andThrow($exception);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldNotReceive('toReports');
    $usecase = new FindReportsByUserIdUsecase($repository, $adapter);

    expect(fn () => $usecase(new FindReportsByUserIdInputDTO('user-id')))->toThrow($exception);
});

test('propagates a failure adapting the user query result', function (): void {
    $source = ['opaque-result' => ['repository-specific-value']];
    $exception = new RuntimeException('Falha ao adaptar os relatórios.');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('getReportByUserId')->once()->with('user-id')->andReturn($source);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldReceive('toReports')->once()->with($source)->andThrow($exception);
    $usecase = new FindReportsByUserIdUsecase($repository, $adapter);

    expect(fn () => $usecase(new FindReportsByUserIdInputDTO('user-id')))->toThrow($exception);
});
