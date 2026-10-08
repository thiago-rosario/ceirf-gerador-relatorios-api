<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\MonthlyReportCountDataDTO;
use src\Modules\Report\Application\DTO\ReportDataDTO;
use src\Modules\Report\Application\Service\ReportDataMapperService;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Infra\Adapter\ReportQueryResultAdapter;
use Tests\Fixtures\ReportApplicationFixtures;

test('returns typed reports in query order with sequential list keys', function (): void {
    $draft = new ReportEntity(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        id: '550e8400-e29b-41d4-a716-446655440011',
        createdAt: '2020-01-01 08:00:00',
    );
    $adapter = new ReportQueryResultAdapter(new ReportDataMapperService);

    $reports = $adapter->toReports(['generated' => ReportApplicationFixtures::generatedReport(), 'draft' => $draft]);

    expect($reports)->toContainOnlyInstancesOf(ReportDataDTO::class);
    expect(array_keys($reports))->toBe([0, 1]);
    expect(array_column($reports, 'id'))->toBe([
        '550e8400-e29b-41d4-a716-446655440010',
        '550e8400-e29b-41d4-a716-446655440011',
    ]);
    expect(array_column($reports, 'status'))->toBe(['GENERATED', 'DRAFT']);
    expect($reports[0]->cover?->municipality?->name)->toBe('Salvador');
});

test('returns an empty report list for an empty query', function (): void {
    $adapter = new ReportQueryResultAdapter(new ReportDataMapperService);

    $reports = $adapter->toReports([]);

    expect($reports)->toBe([]);
});

test('rejects report query rows that are not domain entities', function (mixed $row): void {
    $adapter = new ReportQueryResultAdapter(new ReportDataMapperService);

    expect(fn () => $adapter->toReports([$row]))->toThrow(UnexpectedValueException::class);
})->with([
    'null row' => [null],
    'attribute array' => [['id' => '550e8400-e29b-41d4-a716-446655440010']],
    'unrelated object' => [new stdClass],
]);

test('maps monthly count rows while preserving zero counts and query order', function (): void {
    $adapter = new ReportQueryResultAdapter(new ReportDataMapperService);
    $data = [
        'october' => ['month' => '2026-10', 'count' => 0],
        'september' => ['month' => '2026-09', 'count' => 8],
    ];

    $counts = $adapter->toMonthlyCounts($data);

    expect($counts)->toEqual([
        new MonthlyReportCountDataDTO('2026-10', 0),
        new MonthlyReportCountDataDTO('2026-09', 8),
    ]);
});

test('returns an empty monthly count list for an empty query', function (): void {
    $adapter = new ReportQueryResultAdapter(new ReportDataMapperService);

    $counts = $adapter->toMonthlyCounts([]);

    expect($counts)->toBe([]);
});

test('rejects monthly count rows with missing or incorrectly typed fields', function (mixed $row): void {
    $adapter = new ReportQueryResultAdapter(new ReportDataMapperService);

    expect(fn () => $adapter->toMonthlyCounts([$row]))->toThrow(UnexpectedValueException::class);
})->with([
    'non array row' => [null],
    'missing month' => [['count' => 8]],
    'missing count' => [['month' => '2026-10']],
    'non string month' => [['month' => 202610, 'count' => 8]],
    'non integer count' => [['month' => '2026-10', 'count' => '8']],
]);
