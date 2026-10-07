<?php

declare(strict_types=1);

namespace src\Modules\Identity\Domain\Trait;

use DateTimeImmutable;
use src\Modules\Identity\Domain\ValueObject\EmailValueObject;

trait MethodsMagicsTraits
{
    use ResolvesDateTimeTrait;
    use ResolvesUuidTrait;

    private function resolveEmail(EmailValueObject|string $email): EmailValueObject
    {
        return $email instanceof EmailValueObject ? $email : new EmailValueObject($email);
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable;
    }
}
