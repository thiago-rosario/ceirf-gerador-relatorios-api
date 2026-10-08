<?php

declare(strict_types=1);

namespace src\Modules\Report\Infra\Trait;

use src\Modules\Report\Domain\Entity\ReportAttachmentEntity;
use src\Modules\Report\Domain\Entity\ReportImageEntity;
use src\Modules\Report\Domain\Enum\ReportAttachmentTypeEnum;
use src\Modules\Report\Domain\ValueObject\ReportFileReferenceValueObject;
use src\Modules\Report\Infra\Mapper\ReportPersistenceMapper;

/**
 * @phpstan-import-type FilePayload from ReportPersistenceMapper
 * @phpstan-import-type ImagePayload from ReportPersistenceMapper
 * @phpstan-import-type AttachmentPayload from ReportPersistenceMapper
 */
trait MapsReportPersistenceMediaTrait
{
    /** @return ImagePayload */
    private function toImage(ReportImageEntity $image): array
    {
        return [
            'id' => $image->id()->value(),
            'file' => $this->toFile($image->file()),
            'order' => $image->order(),
            'caption' => $image->caption(),
        ];
    }

    /** @param ImagePayload $payload */
    private function fromImage(array $payload): ReportImageEntity
    {
        return new ReportImageEntity(
            file: $this->fromFile($payload['file']),
            order: $payload['order'],
            caption: $payload['caption'],
            id: $payload['id'],
        );
    }

    /** @return AttachmentPayload */
    private function toAttachment(ReportAttachmentEntity $attachment): array
    {
        return [
            'id' => $attachment->id()->value(),
            'type' => $attachment->type()->value,
            'file' => $this->toFile($attachment->file()),
            'description' => $attachment->description(),
        ];
    }

    /** @param AttachmentPayload $payload */
    private function fromAttachment(array $payload): ReportAttachmentEntity
    {
        return new ReportAttachmentEntity(
            type: ReportAttachmentTypeEnum::from($payload['type']),
            file: $this->fromFile($payload['file']),
            description: $payload['description'],
            id: $payload['id'],
        );
    }

    /** @return FilePayload */
    private function toFile(ReportFileReferenceValueObject $file): array
    {
        return [
            'storageIdentifier' => $file->storageIdentifier(),
            'fileName' => $file->fileName(),
            'mimeType' => $file->mimeType(),
            'sizeBytes' => $file->sizeBytes(),
            'checksum' => $file->checksum(),
        ];
    }

    /** @param FilePayload $payload */
    private function fromFile(array $payload): ReportFileReferenceValueObject
    {
        return new ReportFileReferenceValueObject(
            storageIdentifier: $payload['storageIdentifier'],
            fileName: $payload['fileName'],
            mimeType: $payload['mimeType'],
            sizeBytes: $payload['sizeBytes'],
            checksum: $payload['checksum'],
        );
    }
}
