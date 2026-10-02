<?php

declare(strict_types=1);

namespace src\Identity\Application\Usecase\User;

use src\Identity\Application\DTO\User\DeactivateUserInputDTO;
use src\Identity\Application\DTO\User\DeactivateUserOutputDTO;
use src\Identity\Application\Exception\UserNotFoundException;
use src\Identity\Application\Interfaces\Usecase\User\DeactivateUserUsecaseInterface;
use src\Identity\Domain\Repository\UserRepositoryInterface;
use src\Identity\Domain\Resolver\UuidResolver;

class DeactivateUserUsecase implements DeactivateUserUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function __invoke(DeactivateUserInputDTO $input): DeactivateUserOutputDTO
    {
        $id = new UuidResolver($input->id);

        $user = $this->repository->findById($id->value());

        if ($user === null) {
            throw new UserNotFoundException;
        }

        $userToDeactivate = clone $user;

        $userToDeactivate->deactivate();

        $userDeactivated = $this->repository->update($userToDeactivate);

        return new DeactivateUserOutputDTO(
            id: $userDeactivated->id()->value(),
            name: $userDeactivated->name(),
            email: $userDeactivated->email()->value(),
            active: $userDeactivated->isActive(),
        );
    }
}
