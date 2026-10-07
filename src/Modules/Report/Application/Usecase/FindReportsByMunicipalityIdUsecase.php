<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\FindReportsByMunicipalityIdInputDTO;
use src\Modules\Report\Application\DTO\ListReportsOutputDTO;
use src\Modules\Report\Application\Interfaces\Adapter\ReportQueryResultAdapterInterface;
use src\Modules\Report\Application\Interfaces\Usecase\FindReportsByMunicipalityIdUsecaseInterface;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

class FindReportsByMunicipalityIdUsecase implements FindReportsByMunicipalityIdUsecaseInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
        private readonly ReportQueryResultAdapterInterface $adapter,
    ) {}

    public function __invoke(FindReportsByMunicipalityIdInputDTO $input): ListReportsOutputDTO
    {
        $reports = $this->repository->findReportByMunicipalityId($input->municipalityId);

        return new ListReportsOutputDTO(
            reports: $reports === null ? [] : $this->adapter->toReports($reports),
        );
    }
}
