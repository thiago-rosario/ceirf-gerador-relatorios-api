<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\FindReportHistoryInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;
use src\Modules\Report\Application\DTO\ReportDataDTO;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Application\Service\ReportDataMapperService;
use src\Modules\Report\Application\Usecase\FindReportHistoryUsecase;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('returns the requested family as report data in repository order', function (): void {
    $rootReportId = '550e8400-e29b-41d4-a716-446655440001';
    $rootReport = new ReportEntity(createdBy: '550e8400-e29b-41d4-a716-446655440000', id: $rootReportId);
    $revision = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        id: '550e8400-e29b-41d4-a716-446655440002',
        rootReportId: $rootReportId,
        parentReportId: $rootReportId,
        revisionNumber: 1,
    );
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findByRootReportId')->once()->with($rootReportId)->andReturn([$revision, $rootReport]);
    $usecase = new FindReportHistoryUsecase($repository, new ReportDataMapperService);

    $output = $usecase(new FindReportHistoryInputDTO($rootReportId));

    expect($output)->toBeInstanceOf(ListReportsOutputDTO::class);
    expect($output->reports)->toContainOnlyInstancesOf(ReportDataDTO::class);
    expect(array_map(fn (ReportDataDTO $report): string => $report->id, $output->reports))->toBe([
        '550e8400-e29b-41d4-a716-446655440002',
        '550e8400-e29b-41d4-a716-446655440001',
    ]);
});

test('returns an empty history when the family has no stored reports', function (): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findByRootReportId')->once()->with('unknown-family')->andReturn([]);
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new FindReportHistoryUsecase($repository, $mapper);

    $output = $usecase(new FindReportHistoryInputDTO('unknown-family'));

    expect($output->reports)->toBe([]);
});

test('propagates a family lookup failure', function (): void {
    $exception = new RuntimeException('Falha ao consultar o histórico.');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findByRootReportId')->once()->with('family-id')->andThrow($exception);
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new FindReportHistoryUsecase($repository, $mapper);

    expect(fn () => $usecase(new FindReportHistoryInputDTO('family-id')))->toThrow($exception);
});
