<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Repositories\Queries;

use src\Modules\Report\Application\Interfaces\Mapper\LegacyReportQueryMapperInterface;
use src\Modules\Report\Application\Interfaces\Mapper\ReportPersistenceMapperInterface;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Model\Report as ReportModel;

class FindByIdReportEloquentRepository
{
    public function __construct(
        private readonly ReportPersistenceMapperInterface $mapper,
        private readonly LegacyReportQueryMapperInterface $legacyMapper,
    ) {}

    public function findById(string $id): ?ReportEntity
    {
        $model = ReportModel::query()->where('uuid', $id)->first();

        return $model === null ? null : ReportEntity::fromModel($model, $this->mapper, $this->legacyMapper);
    }
}
