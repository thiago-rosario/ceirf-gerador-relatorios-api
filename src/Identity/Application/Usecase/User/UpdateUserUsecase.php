<?php

declare(strict_types=1);

namespace src\Identity\Application\Usecase\User;

use src\Identity\Application\DTO\User\UpdateUserInputDTO;
use src\Identity\Application\DTO\User\UpdateUserOutputDTO;
use src\Identity\Application\Exception\UserNotFoundException;
use src\Identity\Application\Interfaces\Usecase\User\UpdateUserUsecaseInterface;
use src\Identity\Domain\Repository\UserRepositoryInterface;
use src\Identity\Domain\Resolver\UuidResolver;

class UpdateUserUsecase implements UpdateUserUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function __invoke(UpdateUserInputDTO $input): UpdateUserOutputDTO
    {
        $user = $this->repository->findById($input->id);

        if ($user === null) {
            throw new UserNotFoundException;
        }

        $userToUpdate = clone $user;

        if ($input->name !== null) {
            $userToUpdate->changeName($input->name);
        }

        if ($input->email !== null) {
            $userToUpdate->changeEmail($input->email);
        }

        if ($input->password !== null) {
            $userToUpdate->changePassword($input->password);
        }

        if ($input->role !== null) {
            $userToUpdate->changeRole($input->role);
        }

        $userUpdated = $this->repository->update($userToUpdate);

        return new UpdateUserOutputDTO(
            id: $userUpdated->id()->value(),
            name: $userUpdated->name(),
            email: $userUpdated->email()->value(),
            role: $userUpdated->role()->value,
            isActive: $userUpdated->isActive(),
            createdAt: $userUpdated->createdAt(),
            updatedAt: $userUpdated->updatedAt(),
        );
    }
}
