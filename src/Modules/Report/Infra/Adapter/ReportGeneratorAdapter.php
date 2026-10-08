<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Adapter;

use LogicException;
use src\Modules\Report\Application\Interfaces\Adapter\ReportGeneratorAdapterInterface;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\ValueObject\GeneratedReportValueObject;

class ReportGeneratorAdapter implements ReportGeneratorAdapterInterface
{
    public function generate(ReportEntity $report): GeneratedReportValueObject
    {
        throw new LogicException('A geração de PDF ainda não foi implementada.');
    }
}
