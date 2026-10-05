<?php

declare(strict_types=1);

namespace src\Identity\Application\Usecase\User;

use src\Identity\Application\DTO\User\FindByIdUserOutputDTO;
use src\Identity\Application\DTO\User\ListAllUserInputDTO;
use src\Identity\Application\DTO\User\ListAllUserOutputDTO;
use src\Identity\Application\Interfaces\Usecase\User\ListAllUserUsecaseInterface;
use src\Identity\Domain\Repository\UserRepositoryInterface;

class ListAllUserUsecase implements ListAllUserUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function __invoke(ListAllUserInputDTO $input): ListAllUserOutputDTO
    {
        $users = $this->repository->findAll($input->filter, $input->orderBy);

        $usersData = [];

        foreach ($users as $user) {
            $usersData[] = new FindByIdUserOutputDTO(
                id: $user->id()->value(),
                name: $user->name(),
                email: $user->email()->value(),
                role: $user->role()->value,
                isActive: $user->isActive(),
                createdAt: $user->createdAt(),
                mustChangePassword: $user->mustChangePassword(),
            );
        }

        return new ListAllUserOutputDTO(users: $usersData);
    }
}
