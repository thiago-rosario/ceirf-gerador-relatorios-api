<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\GenerateReportInputDTO;
use src\Modules\Report\Application\DTO\GenerateReportOutputDTO;
use src\Modules\Report\Application\Interfaces\Adapter\ReportGeneratorAdapterInterface;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Application\Interfaces\Service\ReportFinderServiceInterface;
use src\Modules\Report\Application\Interfaces\Usecase\GenerateReportUsecaseInterface;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;

class GenerateReportUsecase implements GenerateReportUsecaseInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
        private readonly ReportFinderServiceInterface $finder,
        private readonly ReportDataMapperServiceInterface $mapper,
        private readonly ReportGeneratorAdapterInterface $generator,
    ) {}

    public function __invoke(GenerateReportInputDTO $input): GenerateReportOutputDTO
    {
        $report = $this->finder->findById($input->id);

        $reportToGenerate = clone $report;
        $reportToGenerate->validateForGeneration();
        $document = $reportToGenerate->generatedDocument() ?? $this->generator->generate(clone $reportToGenerate);
        $reportToGenerate->registerGeneratedDocument($document);
        $reportGenerated = $this->repository->update($reportToGenerate);

        return new GenerateReportOutputDTO(report: $this->mapper->map($reportGenerated));
    }
}
