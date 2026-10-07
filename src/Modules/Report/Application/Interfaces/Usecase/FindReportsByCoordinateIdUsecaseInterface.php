<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\FindReportsByCoordinateIdInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;

interface FindReportsByCoordinateIdUsecaseInterface
{
    public function __invoke(FindReportsByCoordinateIdInputDTO $input): ListReportsOutputDTO;
}
