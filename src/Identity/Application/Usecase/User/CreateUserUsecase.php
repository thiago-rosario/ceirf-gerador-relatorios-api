<?php

declare(strict_types=1);

namespace src\Identity\Application\Usecase\User;

use src\Identity\Application\DTO\User\CreateUserInputDTO;
use src\Identity\Application\DTO\User\CreateUserOutputDTO;
use src\Identity\Application\Interfaces\Service\PasswordHasherServiceInterface;
use src\Identity\Application\Interfaces\Usecase\User\CreateUserUsecaseInterface;
use src\Identity\Domain\Entity\UserEntity;
use src\Identity\Domain\Repository\UserRepositoryInterface;
use src\Identity\Domain\Validation\UserValidation;

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
