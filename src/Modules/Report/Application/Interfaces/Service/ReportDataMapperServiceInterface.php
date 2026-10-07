<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Service;

use src\Modules\Report\Application\DTO\ReportDataDTO;
use src\Modules\Report\Domain\Entity\ReportEntity;

interface ReportDataMapperServiceInterface
{
    public function map(ReportEntity $report): ReportDataDTO;
}
