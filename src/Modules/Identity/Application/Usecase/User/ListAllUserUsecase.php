<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Usecase\User;

use src\Modules\Identity\Application\DTO\User\FindByIdUserOutputDTO;
use src\Modules\Identity\Application\DTO\User\ListAllUserInputDTO;
use src\Modules\Identity\Application\DTO\User\ListAllUserOutputDTO;
use src\Modules\Identity\Application\Interfaces\Usecase\User\ListAllUserUsecaseInterface;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;

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
