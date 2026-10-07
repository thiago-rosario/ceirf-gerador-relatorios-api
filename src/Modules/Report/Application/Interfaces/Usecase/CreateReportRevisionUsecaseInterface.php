<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Interfaces\Usecase;

use src\Modules\Report\Application\DTO\CreateReportRevisionInputDTO;
use src\Modules\Report\Application\DTO\CreateReportRevisionOutputDTO;

interface CreateReportRevisionUsecaseInterface
{
    public function __invoke(CreateReportRevisionInputDTO $input): CreateReportRevisionOutputDTO;
}
