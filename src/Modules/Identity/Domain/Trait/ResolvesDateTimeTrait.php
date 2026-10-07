<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Trait;

use DateTimeImmutable;
use DateTimeInterface;

trait ResolvesDateTimeTrait
{
    /**
     * Datas mutáveis são copiadas para impedir alterações externas no estado da entidade.
     */
    private function resolveDateTime(DateTimeInterface|string|null $date, ?DateTimeImmutable $fallback = null): DateTimeImmutable
    {
        if ($date instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($date);
        }

        if ($date === null || $date === '') {
            return $fallback ?? new DateTimeImmutable;
        }

        return new DateTimeImmutable($date);
    }
}
