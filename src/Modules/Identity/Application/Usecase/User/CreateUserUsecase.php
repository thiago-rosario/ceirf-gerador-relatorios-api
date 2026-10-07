<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Usecase\User;

use src\Modules\Identity\Application\DTO\User\CreateUserInputDTO;
use src\Modules\Identity\Application\DTO\User\CreateUserOutputDTO;
use src\Modules\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Modules\Identity\Application\Interfaces\Usecase\User\CreateUserUsecaseInterface;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;
use src\Modules\Identity\Domain\Validation\UserValidation;

class CreateUserUsecase implements CreateUserUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly PasswordHasherServiceInterface $hasher,
    ) {}

    public function __invoke(CreateUserInputDTO $input): CreateUserOutputDTO
    {
        UserValidation::validatePassword($input->password);

        $user = new UserEntity(
            name: $input->name,
            email: $input->email,
            password: $this->hasher->hash($input->password),
            role: $input->role,
        );

        $userCreated = $this->repository->insert($user);

        return new CreateUserOutputDTO(
            id: $userCreated->id()->value(),
            name: $userCreated->name(),
            email: $userCreated->email()->value(),
            role: $userCreated->role()->value,
            isActive: $userCreated->isActive(),
            createdAt: $userCreated->createdAt(),
        );
    }
}
