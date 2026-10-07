<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Usecase\User;

use src\Modules\Identity\Application\DTO\User\DeactivateUserInputDTO;
use src\Modules\Identity\Application\DTO\User\DeactivateUserOutputDTO;
use src\Modules\Identity\Application\Exception\UserNotFoundException;
use src\Modules\Identity\Application\Interfaces\Usecase\User\DeactivateUserUsecaseInterface;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;
use src\Modules\Shared\Resolver\UuidResolver;

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
