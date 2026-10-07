<?php

declare(strict_types=1);

namespace src\Modules\Report\Domain\Trait;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use src\Modules\Identity\Domain\Exception\InvalidUserIdException;
use src\Modules\Identity\Domain\Trait\ResolvesDateTimeTrait;
use src\Modules\Identity\Domain\Trait\ResolvesUuidTrait;
use src\Modules\Report\Domain\Exception\InvalidReportDateException;
use src\Modules\Report\Domain\Exception\InvalidReportIdException;
use src\Modules\Shared\Resolver\UuidResolver;

/**
 * Reaproveita a resolução de Identity e traduz suas falhas para os códigos de Report.
 */
trait ResolvesReportValuesTrait
{
    use ResolvesDateTimeTrait {
        resolveDateTime as private resolveIdentityDateTime;
    }
    use ResolvesUuidTrait {
        resolveUuid as private resolveIdentityUuid;
    }

    private function resolveUuid(UuidResolver|string|null $id): UuidResolver
    {
        try {
            return $this->resolveIdentityUuid($id);
        } catch (InvalidUserIdException $exception) {
            throw new InvalidReportIdException(previous: $exception);
        }
    }

    private function resolveDateTime(DateTimeInterface|string|null $date, ?DateTimeImmutable $fallback = null): DateTimeImmutable
    {
        try {
            return $this->resolveIdentityDateTime($date, $fallback);
        } catch (Exception $exception) {
            throw new InvalidReportDateException(previous: $exception);
        }
    }
}
