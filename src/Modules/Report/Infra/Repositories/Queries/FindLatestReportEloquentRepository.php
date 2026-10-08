<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Repositories\Queries;

use Illuminate\Support\Facades\DB;
use src\Modules\Report\Application\Interfaces\Mapper\LegacyReportQueryMapperInterface;
use src\Modules\Report\Application\Interfaces\Mapper\ReportPersistenceMapperInterface;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Model\Report as ReportModel;

class FindLatestReportEloquentRepository
{
    public function __construct(
        private readonly ReportPersistenceMapperInterface $mapper,
        private readonly LegacyReportQueryMapperInterface $legacyMapper,
    ) {}

    public function findLatestByRootReportId(string $rootReportId): ?ReportEntity
    {
        $model = ReportModel::query()
            ->whereIn('report_series_id', DB::table('report_series')->select('id')->where('uuid', $rootReportId))
            ->orderByDesc('revision_number')
            ->orderByDesc('id')
            ->first();

        return $model === null ? null : ReportEntity::fromModel($model, $this->mapper, $this->legacyMapper);
    }
}
