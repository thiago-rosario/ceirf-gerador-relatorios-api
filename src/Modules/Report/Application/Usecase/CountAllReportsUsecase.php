<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\CountAllReportsOutputDTO;
use src\Modules\Report\Application\Interfaces\Usecase\CountAllReportsUsecaseInterface;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

class CountAllReportsUsecase implements CountAllReportsUsecaseInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
    ) {}

    public function __invoke(): CountAllReportsOutputDTO
    {
        return new CountAllReportsOutputDTO(count: $this->repository->countAllReports());
    }
}
