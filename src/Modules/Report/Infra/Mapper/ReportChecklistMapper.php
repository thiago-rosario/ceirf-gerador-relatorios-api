<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Mapper;

use src\Modules\Report\Application\DTO\CreateReportInputDTO;
use src\Modules\Report\Application\Interfaces\Mapper\ReportChecklistMapperInterface;
use src\Modules\Report\Domain\Enum\ChecklistAnswerEnum;
use src\Modules\Report\Domain\ValueObject\ReportChecklistValueObject;

class ReportChecklistMapper implements ReportChecklistMapperInterface
{
    public function map(CreateReportInputDTO $input): ReportChecklistValueObject
    {
        return new ReportChecklistValueObject(
            seiConstructionRequest: $this->toAnswer($input->seiConstructionRequest),
            seiLandAndTypologyIdentification: $this->toAnswer($input->seiLandAndTypologyIdentification),
            stateOwnedLand: $this->toAnswer($input->stateOwnedLand),
            simovLegalized: $this->toAnswer($input->simovLegalized),
            compatibleDimensions: $this->toAnswer($input->compatibleDimensions),
            slopeOrLevelRisk: $this->toAnswer($input->slopeOrLevelRisk),
            stormwaterDrainage: $this->toAnswer($input->stormwaterDrainage),
            floodHistory: $this->toAnswer($input->floodHistory),
            electricitySupply: $this->toAnswer($input->electricitySupply),
            waterSupply: $this->toAnswer($input->waterSupply),
            sewageSupply: $this->toAnswer($input->sewageSupply),
            pavingAndSidewalk: $this->toAnswer($input->pavingAndSidewalk),
            regularWasteCollection: $this->toAnswer($input->regularWasteCollection),
            demolitionRequired: $this->toAnswer($input->demolitionRequired),
            easyPublicAccess: $this->toAnswer($input->easyPublicAccess),
            domainStripOrNonBuildableArea: $this->toAnswer($input->domainStripOrNonBuildableArea),
            technicalFeasibilityReport: $this->toAnswer($input->technicalFeasibilityReport),
            reportAttachedToSei: $this->toAnswer($input->reportAttachedToSei),
            worksDashboardUpdated: $this->toAnswer($input->worksDashboardUpdated),
            environmentalProtectionArea: $this->toAnswer($input->environmentalProtectionArea),
        );
    }

    private function toAnswer(?string $answer): ?ChecklistAnswerEnum
    {
        return $answer === null ? null : ChecklistAnswerEnum::from($answer);
    }
}
