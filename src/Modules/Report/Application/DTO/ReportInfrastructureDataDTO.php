<?php

declare(strict_types=1);

namespace src\Modules\Report\Application\DTO;

readonly class ReportInfrastructureDataDTO
{
    public function __construct(
        public ?string $waterNetwork,
        public ?string $highVoltageNetwork,
        public ?string $lowVoltageNetwork,
        public ?string $sewageNetwork,
        public ?string $telephony,
        public ?string $publicLighting,
        public ?string $internet,
        public ?string $wasteCollection,
        public ?string $paving,
        public ?string $existingBuildings,
    ) {}
}
