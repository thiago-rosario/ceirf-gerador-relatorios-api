<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\FindReportHistoryInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;

interface FindReportHistoryUsecaseInterface
{
    public function __invoke(FindReportHistoryInputDTO $input): ListReportsOutputDTO;
}
