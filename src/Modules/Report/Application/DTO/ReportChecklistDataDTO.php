<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class ReportChecklistDataDTO
{
    public function __construct(
        public ?string $seiConstructionRequest,
        public ?string $seiLandAndTypologyIdentification,
        public ?string $stateOwnedLand,
        public ?string $simovLegalized,
        public ?string $compatibleDimensions,
        public ?string $slopeOrLevelRisk,
        public ?string $stormwaterDrainage,
        public ?string $floodHistory,
        public ?string $electricitySupply,
        public ?string $waterSupply,
        public ?string $sewageSupply,
        public ?string $pavingAndSidewalk,
        public ?string $regularWasteCollection,
        public ?string $demolitionRequired,
        public ?string $easyPublicAccess,
        public ?string $domainStripOrNonBuildableArea,
        public ?string $technicalFeasibilityReport,
        public ?string $reportAttachedToSei,
        public ?string $worksDashboardUpdated,
        public ?string $environmentalProtectionArea,
    ) {}
}
