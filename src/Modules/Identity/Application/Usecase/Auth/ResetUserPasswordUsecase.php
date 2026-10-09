<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Usecase\Auth;

use src\Modules\Identity\Application\DTO\Auth\ResetUserPasswordInputDTO;
use src\Modules\Identity\Application\DTO\Auth\ResetUserPasswordOutputDTO;
use src\Modules\Identity\Application\Exception\UserNotFoundException;
use src\Modules\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\Auth\ResetUserPasswordUsecaseInterface;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;
use src\Modules\Shared\Resolver\UuidResolver;

class ResetUserPasswordUsecase implements ResetUserPasswordUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly PasswordHasherServiceInterface $hasher,
    ) {}

    public function __invoke(ResetUserPasswordInputDTO $input): ResetUserPasswordOutputDTO
    {
        $id = new UuidResolver($input->id);
        $user = $this->repository->findById($id->value());

        if ($user === null) {
            throw new UserNotFoundException;
        }

        $userToReset = clone $user;
        $userToReset->resetPassword();

        $userWithHashedPassword = new UserEntity(
            id: $userToReset->id(),
            name: $userToReset->name(),
            email: $userToReset->email(),
            password: $this->hasher->hash($userToReset->password()),
            isActive: $userToReset->isActive(),
            role: $userToReset->role(),
            mustChangePassword: $userToReset->mustChangePassword(),
            createdAt: $userToReset->createdAt(),
            updatedAt: $userToReset->updatedAt(),
            coordinationId: $userToReset->coordinationId(),
        );

        $updatedUser = $this->repository->update($userWithHashedPassword);

        return new ResetUserPasswordOutputDTO(
            id: $updatedUser->id()->value(),
            mustChangePassword: $updatedUser->mustChangePassword(),
        );
    }
}
