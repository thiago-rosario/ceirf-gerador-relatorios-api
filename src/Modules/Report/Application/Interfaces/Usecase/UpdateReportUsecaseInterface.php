<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\UpdateReportInputDTO;
use src\Modules\Report\Application\DTO\UpdateReportOutputDTO;

interface UpdateReportUsecaseInterface
{
    public function __invoke(UpdateReportInputDTO $input): UpdateReportOutputDTO;
}
