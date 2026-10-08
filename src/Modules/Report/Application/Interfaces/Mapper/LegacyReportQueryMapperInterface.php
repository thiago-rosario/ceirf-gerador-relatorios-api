<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Mapper;

use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Model\Report as ReportModel;

interface LegacyReportQueryMapperInterface
{
    public function fromModel(ReportModel $model): ReportEntity;
}
