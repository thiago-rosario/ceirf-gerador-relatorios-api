<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\FindReportsByMunicipalityIdInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;

interface FindReportsByMunicipalityIdUsecaseInterface
{
    public function __invoke(FindReportsByMunicipalityIdInputDTO $input): ListReportsOutputDTO;
}
