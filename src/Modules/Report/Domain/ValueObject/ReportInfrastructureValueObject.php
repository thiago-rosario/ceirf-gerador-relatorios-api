<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\ValueObject;

use src\Modules\Report\Domain\Enum\ChecklistAnswerEnum;

readonly class ReportInfrastructureValueObject
{
    public function __construct(
        private ?ChecklistAnswerEnum $waterNetwork = null,
        private ?ChecklistAnswerEnum $highVoltageNetwork = null,
        private ?ChecklistAnswerEnum $lowVoltageNetwork = null,
        private ?ChecklistAnswerEnum $sewageNetwork = null,
        private ?ChecklistAnswerEnum $telephony = null,
        private ?ChecklistAnswerEnum $publicLighting = null,
        private ?ChecklistAnswerEnum $internet = null,
        private ?ChecklistAnswerEnum $wasteCollection = null,
        private ?ChecklistAnswerEnum $paving = null,
        private ?ChecklistAnswerEnum $existingBuildings = null,
    ) {}

    public function waterNetwork(): ?ChecklistAnswerEnum
    {
        return $this->waterNetwork;
    }

    public function highVoltageNetwork(): ?ChecklistAnswerEnum
    {
        return $this->highVoltageNetwork;
    }

    public function lowVoltageNetwork(): ?ChecklistAnswerEnum
    {
        return $this->lowVoltageNetwork;
    }

    public function sewageNetwork(): ?ChecklistAnswerEnum
    {
        return $this->sewageNetwork;
    }

    public function telephony(): ?ChecklistAnswerEnum
    {
        return $this->telephony;
    }

    public function publicLighting(): ?ChecklistAnswerEnum
    {
        return $this->publicLighting;
    }

    public function internet(): ?ChecklistAnswerEnum
    {
        return $this->internet;
    }

    public function wasteCollection(): ?ChecklistAnswerEnum
    {
        return $this->wasteCollection;
    }

    public function paving(): ?ChecklistAnswerEnum
    {
        return $this->paving;
    }

    public function existingBuildings(): ?ChecklistAnswerEnum
    {
        return $this->existingBuildings;
    }

    /**
     * @return array{
     *     waterNetwork: ?ChecklistAnswerEnum,
     *     highVoltageNetwork: ?ChecklistAnswerEnum,
     *     lowVoltageNetwork: ?ChecklistAnswerEnum,
     *     sewageNetwork: ?ChecklistAnswerEnum,
     *     telephony: ?ChecklistAnswerEnum,
     *     publicLighting: ?ChecklistAnswerEnum,
     *     internet: ?ChecklistAnswerEnum,
     *     wasteCollection: ?ChecklistAnswerEnum,
     *     paving: ?ChecklistAnswerEnum,
     *     existingBuildings: ?ChecklistAnswerEnum,
     * }
     */
    public function answers(): array
    {
        return [
            'waterNetwork' => $this->waterNetwork,
            'highVoltageNetwork' => $this->highVoltageNetwork,
            'lowVoltageNetwork' => $this->lowVoltageNetwork,
            'sewageNetwork' => $this->sewageNetwork,
            'telephony' => $this->telephony,
            'publicLighting' => $this->publicLighting,
            'internet' => $this->internet,
            'wasteCollection' => $this->wasteCollection,
            'paving' => $this->paving,
            'existingBuildings' => $this->existingBuildings,
        ];
    }
}
