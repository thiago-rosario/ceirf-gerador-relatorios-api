<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\CreateReportRevisionInputDTO;
use src\Modules\Report\Application\DTO\CreateReportRevisionOutputDTO;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Application\Interfaces\Service\ReportFinderServiceInterface;
use src\Modules\Report\Application\Interfaces\Usecase\CreateReportRevisionUsecaseInterface;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

class CreateReportRevisionUsecase implements CreateReportRevisionUsecaseInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
        private readonly ReportFinderServiceInterface $finder,
        private readonly ReportDataMapperServiceInterface $mapper,
    ) {}

    public function __invoke(CreateReportRevisionInputDTO $input): CreateReportRevisionOutputDTO
    {
        $report = $this->finder->findById($input->id);
        $revision = $report->createRevision($input->createdBy);
        $revisionCreated = $this->repository->insert($revision);

        return new CreateReportRevisionOutputDTO(report: $this->mapper->map($revisionCreated));
    }
}
