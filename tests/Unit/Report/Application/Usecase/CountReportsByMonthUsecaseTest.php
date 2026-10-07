<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\CountReportsByMonthInputDTO;
use src\Modules\Report\Application\DTO\CountReportsByMonthOutputDTO;
use src\Modules\Report\Application\DTO\MonthlyReportCountDataDTO;
use src\Modules\Report\Application\Interfaces\Adapter\ReportQueryResultAdapterInterface;
use src\Modules\Report\Application\Usecase\CountReportsByMonthUsecase;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('returns typed monthly counts and preserves the requested month', function (): void {
    $source = ['opaque-result' => ['repository-specific-value']];
    $counts = [new MonthlyReportCountDataDTO('2026-09', 8), new MonthlyReportCountDataDTO('2026-10', 0)];
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('countReportsByMonth')->once()->with('2026-10')->andReturn($source);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldReceive('toMonthlyCounts')->once()->with($source)->andReturn($counts);
    $usecase = new CountReportsByMonthUsecase($repository, $adapter);

    $output = $usecase(new CountReportsByMonthInputDTO('2026-10'));

    expect($output)->toBeInstanceOf(CountReportsByMonthOutputDTO::class);
    expect($output->month)->toBe('2026-10');
    expect($output->counts)->toBe($counts);
});

test('returns no monthly counts without adapting a null result', function (): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('countReportsByMonth')->once()->with('2026-10')->andReturnNull();
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldNotReceive('toMonthlyCounts');
    $usecase = new CountReportsByMonthUsecase($repository, $adapter);

    $output = $usecase(new CountReportsByMonthInputDTO('2026-10'));

    expect($output->month)->toBe('2026-10');
    expect($output->counts)->toBe([]);
});

test('adapts an empty monthly count result', function (): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('countReportsByMonth')->once()->with('2026-10')->andReturn([]);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldReceive('toMonthlyCounts')->once()->with([])->andReturn([]);
    $usecase = new CountReportsByMonthUsecase($repository, $adapter);

    $output = $usecase(new CountReportsByMonthInputDTO('2026-10'));

    expect($output->counts)->toBe([]);
});

test('propagates a monthly count failure before adapting results', function (): void {
    $exception = new RuntimeException('Falha ao contar os relatórios do mês.');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('countReportsByMonth')->once()->with('2026-10')->andThrow($exception);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldNotReceive('toMonthlyCounts');
    $usecase = new CountReportsByMonthUsecase($repository, $adapter);

    expect(fn () => $usecase(new CountReportsByMonthInputDTO('2026-10')))->toThrow($exception);
});

test('propagates a failure adapting monthly counts', function (): void {
    $source = ['opaque-result' => ['repository-specific-value']];
    $exception = new RuntimeException('Falha ao adaptar as contagens mensais.');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('countReportsByMonth')->once()->with('2026-10')->andReturn($source);
    $adapter = Mockery::mock(ReportQueryResultAdapterInterface::class);
    $adapter->shouldReceive('toMonthlyCounts')->once()->with($source)->andThrow($exception);
    $usecase = new CountReportsByMonthUsecase($repository, $adapter);

    expect(fn () => $usecase(new CountReportsByMonthInputDTO('2026-10')))->toThrow($exception);
});
