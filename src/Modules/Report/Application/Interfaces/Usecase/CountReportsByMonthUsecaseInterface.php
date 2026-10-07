<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\CountReportsByMonthInputDTO;
use src\Modules\Report\Application\DTO\CountReportsByMonthOutputDTO;

interface CountReportsByMonthUsecaseInterface
{
    public function __invoke(CountReportsByMonthInputDTO $input): CountReportsByMonthOutputDTO;
}
