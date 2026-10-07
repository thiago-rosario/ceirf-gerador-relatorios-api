<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\FindLatestReportInputDTO;
use src\Modules\Report\Application\DTO\FindLatestReportOutputDTO;

interface FindLatestReportUsecaseInterface
{
    public function __invoke(FindLatestReportInputDTO $input): FindLatestReportOutputDTO;
}
