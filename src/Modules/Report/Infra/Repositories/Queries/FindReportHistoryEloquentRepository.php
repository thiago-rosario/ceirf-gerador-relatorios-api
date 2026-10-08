<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Repositories\Queries;

use Illuminate\Support\Facades\DB;
use src\Modules\Report\Application\Interfaces\Mapper\LegacyReportQueryMapperInterface;
use src\Modules\Report\Application\Interfaces\Mapper\ReportPersistenceMapperInterface;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Model\Report as ReportModel;

class FindReportHistoryEloquentRepository
{
    public function __construct(
        private readonly ReportPersistenceMapperInterface $mapper,
        private readonly LegacyReportQueryMapperInterface $legacyMapper,
    ) {}

    /** @return list<ReportEntity> */
    public function findByRootReportId(string $rootReportId): array
    {
        $models = ReportModel::query()
            ->whereIn('report_series_id', DB::table('report_series')->select('id')->where('uuid', $rootReportId))
            ->orderBy('revision_number')
            ->orderBy('id')
            ->get();

        return array_values($models
            ->map(fn (ReportModel $model): ReportEntity => ReportEntity::fromModel($model, $this->mapper, $this->legacyMapper))
            ->all());
    }
}
