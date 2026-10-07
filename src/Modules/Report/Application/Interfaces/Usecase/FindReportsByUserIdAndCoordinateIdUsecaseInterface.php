<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\FindReportsByUserIdAndCoordinateIdInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;

interface FindReportsByUserIdAndCoordinateIdUsecaseInterface
{
    public function __invoke(FindReportsByUserIdAndCoordinateIdInputDTO $input): ListReportsOutputDTO;
}
