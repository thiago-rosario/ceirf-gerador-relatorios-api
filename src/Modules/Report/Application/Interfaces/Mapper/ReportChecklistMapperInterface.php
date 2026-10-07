<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Mapper;

use src\Modules\Report\Application\DTO\CreateReportInputDTO;
use src\Modules\Report\Domain\ValueObject\ReportChecklistValueObject;

interface ReportChecklistMapperInterface
{
    public function map(CreateReportInputDTO $input): ReportChecklistValueObject;
}
