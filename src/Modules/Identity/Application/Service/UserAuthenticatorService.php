<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Service;

use src\Modules\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Modules\Identity\Application\Interfaces\Service\UserAuthenticatorServiceInterface;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;
use src\Modules\Identity\Domain\ValueObject\EmailValueObject;

class UserAuthenticatorService implements UserAuthenticatorServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly PasswordHasherServiceInterface $hasher,
    ) {}

    public function authenticate(EmailValueObject $email, string $password): ?UserEntity
    {
        $user = $this->repository->findByEmail($email->value());

        if ($user === null || ! $this->hasher->verify($password, $user->password())) {
            return null;
        }

        return $user;
    }
}
