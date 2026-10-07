<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\CountAllReportsOutputDTO;
use src\Modules\Report\Application\Usecase\CountAllReportsUsecase;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

afterEach(function (): void {
    Mockery::close();
});

test('returns the total supplied by the repository', function (int $count): void {
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('countAllReports')->once()->withNoArgs()->andReturn($count);
    $usecase = new CountAllReportsUsecase($repository);

    $output = $usecase();

    expect($output)->toBeInstanceOf(CountAllReportsOutputDTO::class);
    expect($output->count)->toBe($count);
})->with([
    'no reports' => 0,
    'existing reports' => 42,
]);

test('propagates a count failure', function (): void {
    $exception = new RuntimeException('Falha ao contar os relatórios.');
    $repository = Mockery::mock(ReportRepositoryInterface::class);
    $repository->shouldReceive('countAllReports')->once()->withNoArgs()->andThrow($exception);
    $usecase = new CountAllReportsUsecase($repository);

    expect(fn () => $usecase())->toThrow($exception);
});
