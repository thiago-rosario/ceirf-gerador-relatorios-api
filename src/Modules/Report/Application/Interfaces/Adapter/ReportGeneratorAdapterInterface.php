<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Adapter;

use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\ValueObject\GeneratedReportValueObject;

interface ReportGeneratorAdapterInterface
{
    public function generate(ReportEntity $report): GeneratedReportValueObject;
}
