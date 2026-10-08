<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Trait;

use Illuminate\Support\Facades\DB;
use src\Modules\Report\Domain\Entity\ReportAttachmentEntity;
use src\Modules\Report\Domain\Entity\ReportImageEntity;
use src\Modules\Report\Domain\Enum\ReportAttachmentTypeEnum;
use src\Modules\Report\Domain\ValueObject\ReportAttachmentsValueObject;
use src\Modules\Report\Domain\ValueObject\ReportChecklistValueObject;
use src\Modules\Report\Domain\ValueObject\ReportFileReferenceValueObject;
use stdClass;
use UnexpectedValueException;

trait MapsLegacyReportMediaTrait
{
    /**
     * @return array{locationMap: ReportImageEntity|null, municipalityInStateMap: ReportImageEntity|null, preImplementation: ReportImageEntity|null, photographs: list<ReportImageEntity>, uploadedImages: list<ReportImageEntity>}
     */
    private function images(int $reportId): array
    {
        $rows = DB::table('report_images')->where('report_id', $reportId)->orderBy('id')->get();
        $nextOrder = 1;

        foreach ($rows as $row) {
            if ($row->position !== null) {
                $nextOrder = max($nextOrder, (int) $row->position + 1);
            }
        }

        $result = ['locationMap' => null, 'municipalityInStateMap' => null, 'preImplementation' => null, 'photographs' => [], 'uploadedImages' => []];

        foreach ($rows as $row) {
            $image = new ReportImageEntity(
                id: (string) $row->uuid,
                file: $this->file($row),
                order: $row->position === null ? $nextOrder++ : (int) $row->position,
                caption: (string) ($row->caption ?? ''),
            );
            $result['uploadedImages'][] = $image;
            $role = match ((string) $row->type) {
                'location_map' => 'locationMap',
                'municipality_in_state_map' => 'municipalityInStateMap',
                'pre_implementation' => 'preImplementation',
                'photographic_documentation' => 'photographs',
                default => null,
            };

            if ($role === 'photographs') {
                $result['photographs'][] = $image;
            } elseif ($role !== null) {
                if ($result[$role] !== null) {
                    throw new UnexpectedValueException('O relatório legado possui mais de uma figura para a mesma seção.');
                }

                $result[$role] = $image;
            }
        }

        usort($result['photographs'], fn (ReportImageEntity $left, ReportImageEntity $right): int => $left->order() <=> $right->order());

        return $result;
    }

    private function attachments(int $reportId, ?ReportChecklistValueObject $checklist): ?ReportAttachmentsValueObject
    {
        $rows = DB::table('report_attachments')->where('report_id', $reportId)->orderBy('position')->orderBy('id')->get();
        $municipalityMap = null;
        $topographicPlan = null;
        $others = [];

        foreach ($rows as $row) {
            $type = ReportAttachmentTypeEnum::tryFrom((string) $row->type);

            if ($type === null) {
                throw new UnexpectedValueException('O relatório legado possui um tipo de anexo desconhecido.');
            }

            $attachment = new ReportAttachmentEntity(
                id: (string) $row->uuid,
                type: $type,
                file: $this->file($row),
                description: (string) ($row->description ?? ''),
            );

            if ($type === ReportAttachmentTypeEnum::OTHER) {
                $others[] = $attachment;
            } elseif ($type === ReportAttachmentTypeEnum::MUNICIPALITY_LOCATION_MAP) {
                if ($municipalityMap !== null) {
                    throw new UnexpectedValueException('O relatório legado possui mais de um mapa de localização anexado.');
                }

                $municipalityMap = $attachment;
            } else {
                if ($topographicPlan !== null) {
                    throw new UnexpectedValueException('O relatório legado possui mais de uma planta topográfica anexada.');
                }

                $topographicPlan = $attachment;
            }
        }

        return $checklist === null && $rows->isEmpty() ? null : new ReportAttachmentsValueObject(
            checklist: $checklist,
            municipalityLocationMap: $municipalityMap,
            topographicPlan: $topographicPlan,
            others: $others,
        );
    }

    private function file(stdClass $row): ReportFileReferenceValueObject
    {
        return new ReportFileReferenceValueObject(
            storageIdentifier: (string) $row->storage_path,
            fileName: (string) $row->original_name,
            mimeType: (string) $row->mime_type,
            sizeBytes: (int) $row->size_bytes,
            checksum: (string) $row->file_hash,
        );
    }
}
