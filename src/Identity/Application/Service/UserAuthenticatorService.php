<?php

declare(strict_types=1);

namespace src\Identity\Application\Service;

use src\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Identity\Application\Interfaces\Service\UserAuthenticatorServiceInterface;
use src\Identity\Domain\Entity\UserEntity;
use src\Identity\Domain\Repository\UserRepositoryInterface;
use src\Identity\Domain\ValueObject\EmailValueObject;

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
