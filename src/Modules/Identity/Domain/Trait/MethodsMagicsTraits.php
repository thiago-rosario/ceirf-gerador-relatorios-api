<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Trait;

use DateTimeImmutable;
use DateTimeInterface;
use src\Modules\Identity\Domain\ValueObject\EmailValueObject;
use src\Modules\Shared\Resolver\UuidResolver;

trait MethodsMagicsTraits
{
    private function resolveUuid(UuidResolver|string|null $id): UuidResolver
    {
        if ($id instanceof UuidResolver) {
            return $id;
        }

        return $id === null ? UuidResolver::random() : new UuidResolver($id);
    }

    private function resolveEmail(EmailValueObject|string $email): EmailValueObject
    {
        return $email instanceof EmailValueObject ? $email : new EmailValueObject($email);
    }

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

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }
}
