<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

use src\Modules\Report\Domain\Enum\ChecklistAnswerEnum;

readonly class ReportChecklistValueObject
{
    public function __construct(
        private ?ChecklistAnswerEnum $seiConstructionRequest = null,
        private ?ChecklistAnswerEnum $seiLandAndTypologyIdentification = null,
        private ?ChecklistAnswerEnum $stateOwnedLand = null,
        private ?ChecklistAnswerEnum $simovLegalized = null,
        private ?ChecklistAnswerEnum $compatibleDimensions = null,
        private ?ChecklistAnswerEnum $slopeOrLevelRisk = null,
        private ?ChecklistAnswerEnum $stormwaterDrainage = null,
        private ?ChecklistAnswerEnum $floodHistory = null,
        private ?ChecklistAnswerEnum $electricitySupply = null,
        private ?ChecklistAnswerEnum $waterSupply = null,
        private ?ChecklistAnswerEnum $sewageSupply = null,
        private ?ChecklistAnswerEnum $pavingAndSidewalk = null,
        private ?ChecklistAnswerEnum $regularWasteCollection = null,
        private ?ChecklistAnswerEnum $demolitionRequired = null,
        private ?ChecklistAnswerEnum $easyPublicAccess = null,
        private ?ChecklistAnswerEnum $domainStripOrNonBuildableArea = null,
        private ?ChecklistAnswerEnum $technicalFeasibilityReport = null,
        private ?ChecklistAnswerEnum $reportAttachedToSei = null,
        private ?ChecklistAnswerEnum $worksDashboardUpdated = null,
        private ?ChecklistAnswerEnum $environmentalProtectionArea = null,
    ) {}

    public function seiConstructionRequest(): ?ChecklistAnswerEnum
    {
        return $this->seiConstructionRequest;
    }

    public function seiLandAndTypologyIdentification(): ?ChecklistAnswerEnum
    {
        return $this->seiLandAndTypologyIdentification;
    }

    public function stateOwnedLand(): ?ChecklistAnswerEnum
    {
        return $this->stateOwnedLand;
    }

    public function simovLegalized(): ?ChecklistAnswerEnum
    {
        return $this->simovLegalized;
    }

    public function compatibleDimensions(): ?ChecklistAnswerEnum
    {
        return $this->compatibleDimensions;
    }

    public function slopeOrLevelRisk(): ?ChecklistAnswerEnum
    {
        return $this->slopeOrLevelRisk;
    }

    public function stormwaterDrainage(): ?ChecklistAnswerEnum
    {
        return $this->stormwaterDrainage;
    }

    public function floodHistory(): ?ChecklistAnswerEnum
    {
        return $this->floodHistory;
    }

    public function electricitySupply(): ?ChecklistAnswerEnum
    {
        return $this->electricitySupply;
    }

    public function waterSupply(): ?ChecklistAnswerEnum
    {
        return $this->waterSupply;
    }

    public function sewageSupply(): ?ChecklistAnswerEnum
    {
        return $this->sewageSupply;
    }

    public function pavingAndSidewalk(): ?ChecklistAnswerEnum
    {
        return $this->pavingAndSidewalk;
    }

    public function regularWasteCollection(): ?ChecklistAnswerEnum
    {
        return $this->regularWasteCollection;
    }

    public function demolitionRequired(): ?ChecklistAnswerEnum
    {
        return $this->demolitionRequired;
    }

    public function easyPublicAccess(): ?ChecklistAnswerEnum
    {
        return $this->easyPublicAccess;
    }

    public function domainStripOrNonBuildableArea(): ?ChecklistAnswerEnum
    {
        return $this->domainStripOrNonBuildableArea;
    }

    public function technicalFeasibilityReport(): ?ChecklistAnswerEnum
    {
        return $this->technicalFeasibilityReport;
    }

    public function reportAttachedToSei(): ?ChecklistAnswerEnum
    {
        return $this->reportAttachedToSei;
    }

    public function worksDashboardUpdated(): ?ChecklistAnswerEnum
    {
        return $this->worksDashboardUpdated;
    }

    public function environmentalProtectionArea(): ?ChecklistAnswerEnum
    {
        return $this->environmentalProtectionArea;
    }

    /**
     * @return array{
     *     seiConstructionRequest: ?ChecklistAnswerEnum,
     *     seiLandAndTypologyIdentification: ?ChecklistAnswerEnum,
     *     stateOwnedLand: ?ChecklistAnswerEnum,
     *     simovLegalized: ?ChecklistAnswerEnum,
     *     compatibleDimensions: ?ChecklistAnswerEnum,
     *     slopeOrLevelRisk: ?ChecklistAnswerEnum,
     *     stormwaterDrainage: ?ChecklistAnswerEnum,
     *     floodHistory: ?ChecklistAnswerEnum,
     *     electricitySupply: ?ChecklistAnswerEnum,
     *     waterSupply: ?ChecklistAnswerEnum,
     *     sewageSupply: ?ChecklistAnswerEnum,
     *     pavingAndSidewalk: ?ChecklistAnswerEnum,
     *     regularWasteCollection: ?ChecklistAnswerEnum,
     *     demolitionRequired: ?ChecklistAnswerEnum,
     *     easyPublicAccess: ?ChecklistAnswerEnum,
     *     domainStripOrNonBuildableArea: ?ChecklistAnswerEnum,
     *     technicalFeasibilityReport: ?ChecklistAnswerEnum,
     *     reportAttachedToSei: ?ChecklistAnswerEnum,
     *     worksDashboardUpdated: ?ChecklistAnswerEnum,
     *     environmentalProtectionArea: ?ChecklistAnswerEnum,
     * }
     */
    public function answers(): array
    {
        return [
            'seiConstructionRequest' => $this->seiConstructionRequest,
            'seiLandAndTypologyIdentification' => $this->seiLandAndTypologyIdentification,
            'stateOwnedLand' => $this->stateOwnedLand,
            'simovLegalized' => $this->simovLegalized,
            'compatibleDimensions' => $this->compatibleDimensions,
            'slopeOrLevelRisk' => $this->slopeOrLevelRisk,
            'stormwaterDrainage' => $this->stormwaterDrainage,
            'floodHistory' => $this->floodHistory,
            'electricitySupply' => $this->electricitySupply,
            'waterSupply' => $this->waterSupply,
            'sewageSupply' => $this->sewageSupply,
            'pavingAndSidewalk' => $this->pavingAndSidewalk,
            'regularWasteCollection' => $this->regularWasteCollection,
            'demolitionRequired' => $this->demolitionRequired,
            'easyPublicAccess' => $this->easyPublicAccess,
            'domainStripOrNonBuildableArea' => $this->domainStripOrNonBuildableArea,
            'technicalFeasibilityReport' => $this->technicalFeasibilityReport,
            'reportAttachedToSei' => $this->reportAttachedToSei,
            'worksDashboardUpdated' => $this->worksDashboardUpdated,
            'environmentalProtectionArea' => $this->environmentalProtectionArea,
        ];
    }
}
