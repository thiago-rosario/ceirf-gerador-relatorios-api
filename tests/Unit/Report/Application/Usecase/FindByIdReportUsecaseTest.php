<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\FindByIdReportInputDTO;
use src\Modules\Report\Application\DTO\FindByIdReportOutputDTO;
use src\Modules\Report\Application\DTO\ReportDataDTO;
use src\Modules\Report\Application\Exception\ReportNotFoundException;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Application\Service\ReportDataMapperService;
use src\Modules\Report\Application\Service\ReportFinderService;
use src\Modules\Report\Application\Usecase\FindByIdReportUsecase;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('returns report data instead of the stored domain entity', function (): void {
    $report = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        id: '550e8400-e29b-41d4-a716-446655440001',
    );
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440001')->andReturn($report);
    $usecase = new FindByIdReportUsecase(new ReportFinderService($repository), new ReportDataMapperService);

    $output = $usecase(new FindByIdReportInputDTO('550e8400-e29b-41d4-a716-446655440001'));

    expect($output)->toBeInstanceOf(FindByIdReportOutputDTO::class);
    expect($output->report)->toBeInstanceOf(ReportDataDTO::class);
    expect($output->report->id)->toBe('550e8400-e29b-41d4-a716-446655440001');
});

test('reports a missing report before mapping its data', function (): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('unknown-report')->andReturnNull();
    $mapper = Mockery::mock(ReportDataMapperServiceInterface::class);
    $mapper->shouldNotReceive('map');
    $usecase = new FindByIdReportUsecase(new ReportFinderService($repository), $mapper);

    expect(fn () => $usecase(new FindByIdReportInputDTO('unknown-report')))->toThrow(ReportNotFoundException::class);
});
