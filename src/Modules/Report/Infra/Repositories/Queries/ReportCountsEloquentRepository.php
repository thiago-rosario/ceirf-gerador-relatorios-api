<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Repositories\Queries;

use DateTimeImmutable;
use InvalidArgumentException;
use src\Modules\Report\Model\Report as ReportModel;

class ReportCountsEloquentRepository
{
    public function countAllReports(): int
    {
        return ReportModel::query()->count();
    }

    /** @return list<array{month: string, count: int}> */
    public function countReportsByMonth(string $month): ?array
    {
        $start = DateTimeImmutable::createFromFormat('!Y-m', $month);

        if ($start === false || $start->format('Y-m') !== $month) {
            throw new InvalidArgumentException('O mês deve estar no formato YYYY-MM.');
        }

        $count = ReportModel::query()
            ->where('created_at', '>=', $start->format('Y-m-d H:i:s'))
            ->where('created_at', '<', $start->modify('+1 month')->format('Y-m-d H:i:s'))
            ->count();

        return $count === 0 ? [] : [['month' => $month, 'count' => $count]];
    }
}
