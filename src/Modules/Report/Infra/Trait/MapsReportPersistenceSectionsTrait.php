<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Trait;

use DateTimeImmutable;
use src\Modules\Report\Domain\Enum\ChecklistAnswerEnum;
use src\Modules\Report\Domain\Enum\ForceEnum;
use src\Modules\Report\Domain\Enum\ReportSizeEnum;
use src\Modules\Report\Domain\ValueObject\MunicipalityValueObject;
use src\Modules\Report\Domain\ValueObject\ReportAttachmentsValueObject;
use src\Modules\Report\Domain\ValueObject\ReportChecklistValueObject;
use src\Modules\Report\Domain\ValueObject\ReportCoverValueObject;
use src\Modules\Report\Domain\ValueObject\SeiNumberValueObject;
use src\Modules\Report\Infra\Mapper\ReportPersistenceMapper;

/**
 * @phpstan-import-type CoverPayload from ReportPersistenceMapper
 * @phpstan-import-type AttachmentsPayload from ReportPersistenceMapper
 */
trait MapsReportPersistenceSectionsTrait
{
    /** @return CoverPayload|null */
    private function toCover(?ReportCoverValueObject $cover): ?array
    {
        if ($cover === null) {
            return null;
        }

        $municipality = $cover->municipality();

        return [
            'municipality' => $municipality === null ? null : [
                'id' => $municipality->id(),
                'name' => $municipality->name(),
                'stateCode' => $municipality->stateCode(),
            ],
            'force' => $cover->force()?->value,
            'size' => $cover->size()?->value,
            'typology' => $cover->typology(),
            'seiNumber' => $cover->seiNumber()?->value(),
        ];
    }

    /** @param CoverPayload|null $payload */
    private function fromCover(?array $payload): ?ReportCoverValueObject
    {
        if ($payload === null) {
            return null;
        }

        $municipality = $payload['municipality'];

        return new ReportCoverValueObject(
            municipality: $municipality === null ? null : new MunicipalityValueObject(
                id: $municipality['id'],
                name: $municipality['name'],
                stateCode: $municipality['stateCode'],
            ),
            force: $payload['force'] === null ? null : ForceEnum::from($payload['force']),
            size: $payload['size'] === null ? null : ReportSizeEnum::from($payload['size']),
            typology: $payload['typology'],
            seiNumber: $payload['seiNumber'] === null ? null : new SeiNumberValueObject($payload['seiNumber']),
        );
    }

    /** @return AttachmentsPayload|null */
    private function toAttachments(?ReportAttachmentsValueObject $attachments): ?array
    {
        if ($attachments === null) {
            return null;
        }

        return [
            'checklist' => $attachments->checklist() === null ? null : $this->toAnswers($attachments->checklist()->answers()),
            'municipalityLocationMap' => $attachments->municipalityLocationMap() === null ? null : $this->toAttachment($attachments->municipalityLocationMap()),
            'topographicPlan' => $attachments->topographicPlan() === null ? null : $this->toAttachment($attachments->topographicPlan()),
            'others' => array_map($this->toAttachment(...), $attachments->others()),
        ];
    }

    /** @param AttachmentsPayload|null $payload */
    private function fromAttachments(?array $payload): ?ReportAttachmentsValueObject
    {
        if ($payload === null) {
            return null;
        }

        return new ReportAttachmentsValueObject(
            checklist: $payload['checklist'] === null ? null : new ReportChecklistValueObject(...$this->fromAnswers($payload['checklist'])),
            municipalityLocationMap: $payload['municipalityLocationMap'] === null ? null : $this->fromAttachment($payload['municipalityLocationMap']),
            topographicPlan: $payload['topographicPlan'] === null ? null : $this->fromAttachment($payload['topographicPlan']),
            others: array_map($this->fromAttachment(...), $payload['others']),
        );
    }

    /**
     * @param  array<string, ChecklistAnswerEnum|null>  $answers
     * @return array<string, string|null>
     */
    private function toAnswers(array $answers): array
    {
        return array_map(fn (?ChecklistAnswerEnum $answer): ?string => $answer?->value, $answers);
    }

    /**
     * @param  array<string, string|null>  $answers
     * @return array<string, ChecklistAnswerEnum|null>
     */
    private function fromAnswers(array $answers): array
    {
        return array_map(fn (?string $answer): ?ChecklistAnswerEnum => $answer === null ? null : ChecklistAnswerEnum::from($answer), $answers);
    }

    private function answerCode(?ChecklistAnswerEnum $answer): ?int
    {
        return match ($answer) {
            ChecklistAnswerEnum::YES => 1,
            ChecklistAnswerEnum::NO => 0,
            ChecklistAnswerEnum::NOT_APPLICABLE => 2,
            null => null,
        };
    }

    private function date(DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d\TH:i:s.uP');
    }
}
