<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Usecase\User;

use src\Modules\Identity\Application\DTO\User\UpdateUserInputDTO;
use src\Modules\Identity\Application\DTO\User\UpdateUserOutputDTO;
use src\Modules\Identity\Application\Exception\UserNotFoundException;
use src\Modules\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\UpdateUserUsecaseInterface;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;
use src\Modules\Identity\Domain\Validation\UserValidation;
use src\Modules\Organization\Application\DTO\CoordinationDataDTO;
use src\Modules\Organization\Domain\Repository\CoordinationRepositoryInterface;

class UpdateUserUsecase implements UpdateUserUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly PasswordHasherServiceInterface $hasher,
        private readonly CoordinationRepositoryInterface $coordinationRepository,
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
            UserValidation::validatePassword($input->password);

            $userToUpdate->changePassword($this->hasher->hash($input->password));
        }

        if ($input->role !== null) {
            $userToUpdate->changeRole($input->role);
        }

        $coordinationProvided = $input->coordinationIdProvided || $input->coordinationId !== null;
        $coordinationId = $coordinationProvided
            ? $input->coordinationId
            : ($input->role === UserRoleEnum::SUPERUSER ? null : $user->coordinationId());
        $coordination = $coordinationId === null ? null : $this->coordinationRepository->findById($coordinationId);
        $isAdministrativeUpdate = $input->name !== null || $input->email !== null || $input->role !== null || $coordinationProvided;

        if ($isAdministrativeUpdate) {
            UserValidation::validateCoordination(
                $userToUpdate->role(),
                $coordinationId,
                $coordination,
                requireActive: $coordinationId !== $user->coordinationId(),
            );
        }

        if ($coordinationId !== $user->coordinationId()) {
            $userToUpdate->changeCoordination($coordinationId);
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
            coordinationId: $userUpdated->coordinationId(),
            coordination: $coordination === null ? null : new CoordinationDataDTO(
                id: $coordination->id(),
                code: $coordination->code(),
                name: $coordination->name(),
            ),
        );
    }
}
