<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\CreateReportInputDTO;
use src\Modules\Report\Application\DTO\CreateReportOutputDTO;

interface CreateReportUsecaseInterface
{
    public function __invoke(CreateReportInputDTO $input): CreateReportOutputDTO;
}
