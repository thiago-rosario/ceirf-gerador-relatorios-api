<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Interfaces\Service;

use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Domain\ValueObject\EmailValueObject;

interface UserAuthenticatorServiceInterface
{
    public function authenticate(EmailValueObject $email, string $password): ?UserEntity;
}
