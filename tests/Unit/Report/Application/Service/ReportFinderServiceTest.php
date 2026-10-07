<?php

declare(strict_types=1);

use src\Modules\Report\Application\Exception\ReportNotFoundException;
use src\Modules\Report\Application\Service\ReportFinderService;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('returns the stored report using the requested identifier', function (): void {
    $report = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        id: '550e8400-e29b-41d4-a716-446655440001',
    );
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('550e8400-e29b-41d4-a716-446655440001')->andReturn($report);
    $finder = new ReportFinderService($repository);

    $foundReport = $finder->findById('550e8400-e29b-41d4-a716-446655440001');

    expect($foundReport)->toBe($report);
});

test('reports a missing resource without adding identifier validation', function (): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('unknown-report')->andReturnNull();
    $finder = new ReportFinderService($repository);

    expect(fn () => $finder->findById('unknown-report'))->toThrow(ReportNotFoundException::class);
});

test('propagates a repository failure', function (): void {
    $exception = new RuntimeException('Falha ao consultar o relatório.');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('report-id')->andThrow($exception);
    $finder = new ReportFinderService($repository);

    expect(fn () => $finder->findById('report-id'))->toThrow($exception);
});
