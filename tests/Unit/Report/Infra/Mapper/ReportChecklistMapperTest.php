<?php

declare(strict_types=1);

use src\Modules\Report\Application\DTO\CreateReportInputDTO;
use src\Modules\Report\Domain\Enum\ChecklistAnswerEnum;
use src\Modules\Report\Domain\ValueObject\ReportChecklistValueObject;
use src\Modules\Report\Infra\Mapper\ReportChecklistMapper;

test('maps each checklist question to its own typed answer', function (): void {
    $input = new CreateReportInputDTO(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        seiConstructionRequest: ChecklistAnswerEnum::YES->value,
        seiLandAndTypologyIdentification: ChecklistAnswerEnum::NO->value,
        stateOwnedLand: ChecklistAnswerEnum::NOT_APPLICABLE->value,
        simovLegalized: ChecklistAnswerEnum::YES->value,
        compatibleDimensions: ChecklistAnswerEnum::NO->value,
        slopeOrLevelRisk: ChecklistAnswerEnum::NOT_APPLICABLE->value,
        stormwaterDrainage: ChecklistAnswerEnum::YES->value,
        floodHistory: ChecklistAnswerEnum::NO->value,
        electricitySupply: ChecklistAnswerEnum::NOT_APPLICABLE->value,
        waterSupply: ChecklistAnswerEnum::YES->value,
        sewageSupply: ChecklistAnswerEnum::NO->value,
        pavingAndSidewalk: ChecklistAnswerEnum::NOT_APPLICABLE->value,
        regularWasteCollection: ChecklistAnswerEnum::YES->value,
        demolitionRequired: ChecklistAnswerEnum::NO->value,
        easyPublicAccess: ChecklistAnswerEnum::NOT_APPLICABLE->value,
        domainStripOrNonBuildableArea: ChecklistAnswerEnum::YES->value,
        technicalFeasibilityReport: ChecklistAnswerEnum::NO->value,
        reportAttachedToSei: ChecklistAnswerEnum::NOT_APPLICABLE->value,
        worksDashboardUpdated: ChecklistAnswerEnum::YES->value,
        environmentalProtectionArea: ChecklistAnswerEnum::NO->value,
    );

    $checklist = (new ReportChecklistMapper)->map($input);

    expect($checklist->answers())->toBe([
        'seiConstructionRequest' => ChecklistAnswerEnum::YES,
        'seiLandAndTypologyIdentification' => ChecklistAnswerEnum::NO,
        'stateOwnedLand' => ChecklistAnswerEnum::NOT_APPLICABLE,
        'simovLegalized' => ChecklistAnswerEnum::YES,
        'compatibleDimensions' => ChecklistAnswerEnum::NO,
        'slopeOrLevelRisk' => ChecklistAnswerEnum::NOT_APPLICABLE,
        'stormwaterDrainage' => ChecklistAnswerEnum::YES,
        'floodHistory' => ChecklistAnswerEnum::NO,
        'electricitySupply' => ChecklistAnswerEnum::NOT_APPLICABLE,
        'waterSupply' => ChecklistAnswerEnum::YES,
        'sewageSupply' => ChecklistAnswerEnum::NO,
        'pavingAndSidewalk' => ChecklistAnswerEnum::NOT_APPLICABLE,
        'regularWasteCollection' => ChecklistAnswerEnum::YES,
        'demolitionRequired' => ChecklistAnswerEnum::NO,
        'easyPublicAccess' => ChecklistAnswerEnum::NOT_APPLICABLE,
        'domainStripOrNonBuildableArea' => ChecklistAnswerEnum::YES,
        'technicalFeasibilityReport' => ChecklistAnswerEnum::NO,
        'reportAttachedToSei' => ChecklistAnswerEnum::NOT_APPLICABLE,
        'worksDashboardUpdated' => ChecklistAnswerEnum::YES,
        'environmentalProtectionArea' => ChecklistAnswerEnum::NO,
    ]);
});

test('preserves unanswered checklist questions in a draft', function (): void {
    $input = new CreateReportInputDTO('550e8400-e29b-41d4-a716-446655440000');

    $checklist = (new ReportChecklistMapper)->map($input);

    expect($checklist)->toEqual(new ReportChecklistValueObject);
});

test('rejects a supplied checklist answer outside the domain enum', function (string $answer): void {
    $input = new CreateReportInputDTO(
        createdBy: '550e8400-e29b-41d4-a716-446655440000',
        stateOwnedLand: $answer,
    );

    expect(fn () => (new ReportChecklistMapper)->map($input))->toThrow(ValueError::class);
})->with([
    'unknown answer' => 'UNKNOWN',
    'empty answer' => '',
    'padded answer' => ' SIM ',
]);
