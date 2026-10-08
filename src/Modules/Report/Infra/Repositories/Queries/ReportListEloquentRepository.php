<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Repositories\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use src\Modules\Identity\Model\User as UserModel;
use src\Modules\Report\Application\Interfaces\Mapper\LegacyReportQueryMapperInterface;
use src\Modules\Report\Application\Interfaces\Mapper\ReportPersistenceMapperInterface;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Model\Report as ReportModel;

class ReportListEloquentRepository
{
    public function __construct(
        private readonly ReportPersistenceMapperInterface $mapper,
        private readonly LegacyReportQueryMapperInterface $legacyMapper,
    ) {}

    /** @return list<ReportEntity> */
    public function getReportByUserId(string $userId): ?array
    {
        return array_values($this->orderedQuery()
            ->whereIn('created_by', UserModel::query()->select('id')->where('uuid', $userId))
            ->get()
            ->map(fn (ReportModel $model): ReportEntity => ReportEntity::fromModel($model, $this->mapper, $this->legacyMapper))
            ->all());
    }

    /** @return list<ReportEntity> */
    public function getReportByCoordinateId(string $coordinateId): ?array
    {
        return array_values($this->orderedQuery()
            ->whereIn('report_type_id', DB::table('report_types')->select('id')->where('coordination_id', $coordinateId))
            ->get()
            ->map(fn (ReportModel $model): ReportEntity => ReportEntity::fromModel($model, $this->mapper, $this->legacyMapper))
            ->all());
    }

    /** @return list<ReportEntity> */
    public function getReportByUserIdAndCoordinateId(string $userId, string $coordinateId): ?array
    {
        return array_values($this->orderedQuery()
            ->whereIn('created_by', UserModel::query()->select('id')->where('uuid', $userId))
            ->whereIn('report_type_id', DB::table('report_types')->select('id')->where('coordination_id', $coordinateId))
            ->get()
            ->map(fn (ReportModel $model): ReportEntity => ReportEntity::fromModel($model, $this->mapper, $this->legacyMapper))
            ->all());
    }

    /** @return list<ReportEntity> */
    public function getDashboardReports(): ?array
    {
        return array_values($this->orderedQuery()
            ->get()
            ->map(fn (ReportModel $model): ReportEntity => ReportEntity::fromModel($model, $this->mapper, $this->legacyMapper))
            ->all());
    }

    /** @return list<ReportEntity> */
    public function findReportByMunicipalityId(string $municipalityId): ?array
    {
        return array_values($this->orderedQuery()
            ->where('municipality_id', $municipalityId)
            ->get()
            ->map(fn (ReportModel $model): ReportEntity => ReportEntity::fromModel($model, $this->mapper, $this->legacyMapper))
            ->all());
    }

    /** @return list<ReportEntity> */
    public function findLatestReportById(string $id): ?array
    {
        $seriesId = ReportModel::query()->where('uuid', $id)->value('report_series_id');

        if ($seriesId === null) {
            return [];
        }

        return array_values(ReportModel::query()
            ->where('report_series_id', $seriesId)
            ->orderByDesc('revision_number')
            ->orderByDesc('id')
            ->limit(1)
            ->get()
            ->map(fn (ReportModel $model): ReportEntity => ReportEntity::fromModel($model, $this->mapper, $this->legacyMapper))
            ->all());
    }

    /** @return Builder<ReportModel> */
    private function orderedQuery(): Builder
    {
        return ReportModel::query()->orderByDesc('created_at')->orderByDesc('id');
    }
}
