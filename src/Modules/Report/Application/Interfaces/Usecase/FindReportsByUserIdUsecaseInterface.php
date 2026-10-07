<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\FindReportsByUserIdInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;

interface FindReportsByUserIdUsecaseInterface
{
    public function __invoke(FindReportsByUserIdInputDTO $input): ListReportsOutputDTO;
}
