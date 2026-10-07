<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\GenerateReportInputDTO;
use src\Modules\Report\Application\DTO\GenerateReportOutputDTO;

interface GenerateReportUsecaseInterface
{
    public function __invoke(GenerateReportInputDTO $input): GenerateReportOutputDTO;
}
