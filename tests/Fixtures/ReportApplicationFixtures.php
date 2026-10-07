<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use DateTimeImmutable;
use src\Modules\Report\Domain\Entity\ReportEntity;
use src\Modules\Report\Domain\Entity\ReportImageEntity;
use src\Modules\Report\Domain\Enum\ChecklistAnswerEnum;
use src\Modules\Report\Domain\Enum\ForceEnum;
use src\Modules\Report\Domain\Enum\ReportSizeEnum;
use src\Modules\Report\Domain\ValueObject\GeneratedReportValueObject;
use src\Modules\Report\Domain\ValueObject\MunicipalityValueObject;
use src\Modules\Report\Domain\ValueObject\ReportAttachmentsValueObject;
use src\Modules\Report\Domain\ValueObject\ReportChecklistValueObject;
use src\Modules\Report\Domain\ValueObject\ReportConclusionValueObject;
use src\Modules\Report\Domain\ValueObject\ReportCoverValueObject;
use src\Modules\Report\Domain\ValueObject\ReportFileReferenceValueObject;
use src\Modules\Report\Domain\ValueObject\ReportGeneralInformationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportInfrastructureValueObject;
use src\Modules\Report\Domain\ValueObject\ReportLocationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportPhotographicDocumentationValueObject;
use src\Modules\Report\Domain\ValueObject\ReportPreImplementationValueObject;
use src\Modules\Report\Domain\ValueObject\SeiNumberValueObject;

final class ReportApplicationFixtures
{
    /**
     * @param  array<string, mixed>  $overrides
     */
    public static function completeReport(array $overrides = []): ReportEntity
    {
        return new ReportEntity(...array_replace([
            'createdBy' => '550e8400-e29b-41d4-a716-446655440000',
            'id' => '550e8400-e29b-41d4-a716-446655440010',
            'createdAt' => '2020-01-01 08:00:00',
            'updatedAt' => '2020-01-01 08:00:00',
            'cover' => new ReportCoverValueObject(
                municipality: new MunicipalityValueObject(1, 'Salvador'),
                force: ForceEnum::PM,
                size: ReportSizeEnum::ONE_B,
                typology: 'Delegacia',
                seiNumber: new SeiNumberValueObject('012.3456.2020.0000012-34'),
            ),
            'generalInformation' => new ReportGeneralInformationValueObject(
                inspectionDate: new DateTimeImmutable('2020-01-01'),
                collaborators: 'Ana Silva e João Santos',
            ),
            'location' => new ReportLocationValueObject(municipalityInStateMap: self::image(1)),
            'infrastructure' => new ReportInfrastructureValueObject(
                waterNetwork: ChecklistAnswerEnum::YES,
                highVoltageNetwork: ChecklistAnswerEnum::NO,
                lowVoltageNetwork: ChecklistAnswerEnum::YES,
                sewageNetwork: ChecklistAnswerEnum::NO,
                telephony: ChecklistAnswerEnum::NOT_APPLICABLE,
                publicLighting: ChecklistAnswerEnum::YES,
                internet: ChecklistAnswerEnum::YES,
                wasteCollection: ChecklistAnswerEnum::YES,
                paving: ChecklistAnswerEnum::NO,
                existingBuildings: ChecklistAnswerEnum::NO,
            ),
            'preImplementation' => new ReportPreImplementationValueObject,
            'photographicDocumentation' => new ReportPhotographicDocumentationValueObject,
            'attachments' => new ReportAttachmentsValueObject(checklist: new ReportChecklistValueObject(
                seiConstructionRequest: ChecklistAnswerEnum::YES,
                seiLandAndTypologyIdentification: ChecklistAnswerEnum::YES,
                stateOwnedLand: ChecklistAnswerEnum::YES,
                simovLegalized: ChecklistAnswerEnum::YES,
                compatibleDimensions: ChecklistAnswerEnum::YES,
                slopeOrLevelRisk: ChecklistAnswerEnum::NO,
                stormwaterDrainage: ChecklistAnswerEnum::YES,
                floodHistory: ChecklistAnswerEnum::NO,
                electricitySupply: ChecklistAnswerEnum::YES,
                waterSupply: ChecklistAnswerEnum::YES,
                sewageSupply: ChecklistAnswerEnum::NO,
                pavingAndSidewalk: ChecklistAnswerEnum::YES,
                regularWasteCollection: ChecklistAnswerEnum::YES,
                demolitionRequired: ChecklistAnswerEnum::NO,
                easyPublicAccess: ChecklistAnswerEnum::YES,
                domainStripOrNonBuildableArea: ChecklistAnswerEnum::NO,
                technicalFeasibilityReport: ChecklistAnswerEnum::YES,
                reportAttachedToSei: ChecklistAnswerEnum::YES,
                worksDashboardUpdated: ChecklistAnswerEnum::YES,
                environmentalProtectionArea: ChecklistAnswerEnum::NOT_APPLICABLE,
            )),
            'conclusion' => new ReportConclusionValueObject('O terreno apresenta condições adequadas à implantação.'),
        ], $overrides));
    }

    public static function generatedReport(): ReportEntity
    {
        $report = self::completeReport();
        $report->registerGeneratedDocument(self::document($report->fileName()));

        return $report;
    }

    public static function document(string $fileName): GeneratedReportValueObject
    {
        return new GeneratedReportValueObject(
            storageIdentifier: 'document:final',
            fileName: $fileName,
            generatedAt: new DateTimeImmutable('2020-01-01 10:00:00'),
        );
    }

    public static function image(int $number): ReportImageEntity
    {
        return new ReportImageEntity(
            file: new ReportFileReferenceValueObject(
                storageIdentifier: sprintf('asset:%d', $number),
                fileName: sprintf('figure-%d.png', $number),
                mimeType: 'image/png',
                sizeBytes: 1024,
                checksum: hash('sha256', sprintf('figure-content-%d', $number)),
            ),
            order: $number,
            caption: sprintf('Figura do terreno %d', $number),
            id: sprintf('550e8400-e29b-41d4-a716-%012d', $number),
        );
    }
}
