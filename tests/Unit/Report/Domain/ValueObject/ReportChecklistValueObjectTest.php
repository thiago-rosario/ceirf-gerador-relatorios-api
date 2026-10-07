<?php

declare(strict_types=1);

use src\Modules\Report\Domain\Enum\ChecklistAnswerEnum;
use src\Modules\Report\Domain\Exception\IncompleteReportException;
use src\Modules\Report\Domain\Validation\ReportChecklistValidation;
use src\Modules\Report\Domain\ValueObject\ReportChecklistValueObject;
use src\Modules\Report\Domain\ValueObject\ReportInfrastructureValueObject;

test('allows unanswered infrastructure and terrain checklist items in a draft', function (): void {
    $infrastructure = new ReportInfrastructureValueObject(waterNetwork: ChecklistAnswerEnum::YES);
    $checklist = new ReportChecklistValueObject(stateOwnedLand: ChecklistAnswerEnum::NO);

    expect($infrastructure->waterNetwork())->toBe(ChecklistAnswerEnum::YES);
    expect($infrastructure->existingBuildings())->toBeNull();
    expect($checklist->stateOwnedLand())->toBe(ChecklistAnswerEnum::NO);
    expect($checklist->environmentalProtectionArea())->toBeNull();
});

test('accepts every answer choice when all infrastructure items are completed', function (ChecklistAnswerEnum $answer): void {
    $answers = [
        'waterNetwork' => $answer,
        'highVoltageNetwork' => $answer,
        'lowVoltageNetwork' => $answer,
        'sewageNetwork' => $answer,
        'telephony' => $answer,
        'publicLighting' => $answer,
        'internet' => $answer,
        'wasteCollection' => $answer,
        'paving' => $answer,
        'existingBuildings' => $answer,
    ];
    $checklist = new ReportInfrastructureValueObject(...$answers);

    ReportChecklistValidation::validateForGeneration($checklist);

    expect($checklist->answers())->toBe($answers);
})->with([
    'yes' => [ChecklistAnswerEnum::YES],
    'no' => [ChecklistAnswerEnum::NO],
    'not applicable' => [ChecklistAnswerEnum::NOT_APPLICABLE],
]);

test('rejects generation when any required infrastructure answer is missing', function (string $question): void {
    $answers = [
        'waterNetwork' => ChecklistAnswerEnum::YES,
        'highVoltageNetwork' => ChecklistAnswerEnum::YES,
        'lowVoltageNetwork' => ChecklistAnswerEnum::YES,
        'sewageNetwork' => ChecklistAnswerEnum::YES,
        'telephony' => ChecklistAnswerEnum::YES,
        'publicLighting' => ChecklistAnswerEnum::YES,
        'internet' => ChecklistAnswerEnum::YES,
        'wasteCollection' => ChecklistAnswerEnum::YES,
        'paving' => ChecklistAnswerEnum::YES,
        'existingBuildings' => ChecklistAnswerEnum::YES,
    ];
    unset($answers[$question]);
    $checklist = new ReportInfrastructureValueObject(...$answers);

    expect(fn () => ReportChecklistValidation::validateForGeneration($checklist))
        ->toThrow(function (IncompleteReportException $exception) use ($question): void {
            expect($exception->getCode())->toBe(2005);
            expect($exception->getMessage())->toBe(
                sprintf('O item %s da seção infraestrutura existente deve estar respondido para gerar o relatório.', $question),
            );
        });
})->with([
    'waterNetwork' => ['waterNetwork'],
    'highVoltageNetwork' => ['highVoltageNetwork'],
    'lowVoltageNetwork' => ['lowVoltageNetwork'],
    'sewageNetwork' => ['sewageNetwork'],
    'telephony' => ['telephony'],
    'publicLighting' => ['publicLighting'],
    'internet' => ['internet'],
    'wasteCollection' => ['wasteCollection'],
    'paving' => ['paving'],
    'existingBuildings' => ['existingBuildings'],
]);

test('accepts every answer choice when all terrain checklist items are completed', function (ChecklistAnswerEnum $answer): void {
    $answers = [
        'seiConstructionRequest' => $answer,
        'seiLandAndTypologyIdentification' => $answer,
        'stateOwnedLand' => $answer,
        'simovLegalized' => $answer,
        'compatibleDimensions' => $answer,
        'slopeOrLevelRisk' => $answer,
        'stormwaterDrainage' => $answer,
        'floodHistory' => $answer,
        'electricitySupply' => $answer,
        'waterSupply' => $answer,
        'sewageSupply' => $answer,
        'pavingAndSidewalk' => $answer,
        'regularWasteCollection' => $answer,
        'demolitionRequired' => $answer,
        'easyPublicAccess' => $answer,
        'domainStripOrNonBuildableArea' => $answer,
        'technicalFeasibilityReport' => $answer,
        'reportAttachedToSei' => $answer,
        'worksDashboardUpdated' => $answer,
        'environmentalProtectionArea' => $answer,
    ];
    $checklist = new ReportChecklistValueObject(...$answers);

    ReportChecklistValidation::validateForGeneration($checklist);

    expect($checklist->answers())->toBe($answers);
})->with([
    'yes' => [ChecklistAnswerEnum::YES],
    'no' => [ChecklistAnswerEnum::NO],
    'not applicable' => [ChecklistAnswerEnum::NOT_APPLICABLE],
]);

test('rejects generation when any required terrain checklist answer is missing', function (string $question): void {
    $answers = [
        'seiConstructionRequest' => ChecklistAnswerEnum::YES,
        'seiLandAndTypologyIdentification' => ChecklistAnswerEnum::YES,
        'stateOwnedLand' => ChecklistAnswerEnum::YES,
        'simovLegalized' => ChecklistAnswerEnum::YES,
        'compatibleDimensions' => ChecklistAnswerEnum::YES,
        'slopeOrLevelRisk' => ChecklistAnswerEnum::YES,
        'stormwaterDrainage' => ChecklistAnswerEnum::YES,
        'floodHistory' => ChecklistAnswerEnum::YES,
        'electricitySupply' => ChecklistAnswerEnum::YES,
        'waterSupply' => ChecklistAnswerEnum::YES,
        'sewageSupply' => ChecklistAnswerEnum::YES,
        'pavingAndSidewalk' => ChecklistAnswerEnum::YES,
        'regularWasteCollection' => ChecklistAnswerEnum::YES,
        'demolitionRequired' => ChecklistAnswerEnum::YES,
        'easyPublicAccess' => ChecklistAnswerEnum::YES,
        'domainStripOrNonBuildableArea' => ChecklistAnswerEnum::YES,
        'technicalFeasibilityReport' => ChecklistAnswerEnum::YES,
        'reportAttachedToSei' => ChecklistAnswerEnum::YES,
        'worksDashboardUpdated' => ChecklistAnswerEnum::YES,
        'environmentalProtectionArea' => ChecklistAnswerEnum::YES,
    ];
    unset($answers[$question]);
    $checklist = new ReportChecklistValueObject(...$answers);

    expect(fn () => ReportChecklistValidation::validateForGeneration($checklist))
        ->toThrow(function (IncompleteReportException $exception) use ($question): void {
            expect($exception->getCode())->toBe(2005);
            expect($exception->getMessage())->toBe(
                sprintf('O item %s da seção checklist do terreno deve estar respondido para gerar o relatório.', $question),
            );
        });
})->with([
    'seiConstructionRequest' => ['seiConstructionRequest'],
    'seiLandAndTypologyIdentification' => ['seiLandAndTypologyIdentification'],
    'stateOwnedLand' => ['stateOwnedLand'],
    'simovLegalized' => ['simovLegalized'],
    'compatibleDimensions' => ['compatibleDimensions'],
    'slopeOrLevelRisk' => ['slopeOrLevelRisk'],
    'stormwaterDrainage' => ['stormwaterDrainage'],
    'floodHistory' => ['floodHistory'],
    'electricitySupply' => ['electricitySupply'],
    'waterSupply' => ['waterSupply'],
    'sewageSupply' => ['sewageSupply'],
    'pavingAndSidewalk' => ['pavingAndSidewalk'],
    'regularWasteCollection' => ['regularWasteCollection'],
    'demolitionRequired' => ['demolitionRequired'],
    'easyPublicAccess' => ['easyPublicAccess'],
    'domainStripOrNonBuildableArea' => ['domainStripOrNonBuildableArea'],
    'technicalFeasibilityReport' => ['technicalFeasibilityReport'],
    'reportAttachedToSei' => ['reportAttachedToSei'],
    'worksDashboardUpdated' => ['worksDashboardUpdated'],
    'environmentalProtectionArea' => ['environmentalProtectionArea'],
]);
