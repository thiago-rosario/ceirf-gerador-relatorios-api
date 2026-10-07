<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\Usecase;

use src\Modules\Report\Application\DTO\UpdateReportInputDTO;
use src\Modules\Report\Application\DTO\UpdateReportOutputDTO;
use src\Modules\Report\Application\Interfaces\Service\ReportDataMapperServiceInterface;
use src\Modules\Report\Application\Interfaces\Service\ReportFinderServiceInterface;
use src\Modules\Report\Application\Interfaces\Usecase\UpdateReportUsecaseInterface;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Repository\ReportRepositoryInterface;
use src\Modules\Report\Domain\ValueObject\ReportAttachmentsValueObject;
use src\Modules\Report\Domain\ValueObject\ReportLocationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportPhotographicDocumentationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportPreImplementationValueObject;

class UpdateReportUsecase implements UpdateReportUsecaseInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
        private readonly ReportFinderServiceInterface $finder,
        private readonly ReportDataMapperServiceInterface $mapper,
    ) {}

    public function __invoke(UpdateReportInputDTO $input): UpdateReportOutputDTO
    {
        $report = $this->finder->findById($input->id);

        if ($input->cover === null
            && $input->generalInformation === null
            && $input->location === null
            && $input->infrastructure === null
            && $input->preImplementation === null
            && $input->photographicDocumentation === null
            && $input->attachments === null
            && $input->conclusion === null) {
            return new UpdateReportOutputDTO(report: $this->mapper->map($report));
        }

        $reportToUpdate = clone $report;
        $this->prepareMediaSectionsForUpdate($reportToUpdate, $input);

        if ($input->cover !== null) {
            $reportToUpdate->changeCover($input->cover);
        }

        if ($input->generalInformation !== null) {
            $reportToUpdate->changeGeneralInformation($input->generalInformation);
        }

        if ($input->location !== null) {
            $reportToUpdate->changeLocation($input->location);
        }

        if ($input->infrastructure !== null) {
            $reportToUpdate->changeInfrastructure($input->infrastructure);
        }

        if ($input->preImplementation !== null) {
            $reportToUpdate->changePreImplementation($input->preImplementation);
        }

        if ($input->photographicDocumentation !== null) {
            $reportToUpdate->changePhotographicDocumentation($input->photographicDocumentation);
        }

        if ($input->attachments !== null) {
            $reportToUpdate->changeAttachments($input->attachments);
        }

        if ($input->conclusion !== null) {
            $reportToUpdate->changeConclusion($input->conclusion);
        }

        $reportUpdated = $this->repository->update($reportToUpdate);

        return new UpdateReportOutputDTO(report: $this->mapper->map($reportUpdated));
    }

    /**
     * Libera as seções substituídas no snapshot para permitir movimentação de mídia no mesmo fluxo.
     */
    private function prepareMediaSectionsForUpdate(ReportEntity $report, UpdateReportInputDTO $input): void
    {
        if ($input->location !== null) {
            $report->changeLocation(new ReportLocationValueObject);
        }

        if ($input->preImplementation !== null) {
            $report->changePreImplementation(new ReportPreImplementationValueObject);
        }

        if ($input->photographicDocumentation !== null) {
            $report->changePhotographicDocumentation(new ReportPhotographicDocumentationValueObject);
        }

        if ($input->attachments !== null) {
            $report->changeAttachments(new ReportAttachmentsValueObject);
        }
    }
}
