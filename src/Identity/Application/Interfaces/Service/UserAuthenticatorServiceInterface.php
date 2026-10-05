<?php

declare(strict_types=1);

namespace src\Identity\Application\Interfaces\Service;

use src\Identity\Domain\Entity\UserEntity;
use src\Identity\Domain\ValueObject\EmailValueObject;

interface UserAuthenticatorServiceInterface
{
    public function authenticate(EmailValueObject $email, string $password): ?UserEntity;
}
