<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Usecase\User;

use src\Modules\Identity\Application\DTO\User\FindByIdUserOutputDTO;
use src\Modules\Identity\Application\DTO\User\ListAllUserInputDTO;
use src\Modules\Identity\Application\DTO\User\ListAllUserOutputDTO;
use src\Modules\Identity\Application\Interfaces\Usecase\User\ListAllUserUsecaseInterface;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;
use src\Modules\Organization\Application\DTO\CoordinationDataDTO;
use src\Modules\Organization\Domain\Repository\CoordinationRepositoryInterface;

class ListAllUserUsecase implements ListAllUserUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly CoordinationRepositoryInterface $coordinationRepository,
    ) {}

    public function __invoke(ListAllUserInputDTO $input): ListAllUserOutputDTO
    {
        $users = $this->repository->findAll($input->filter, $input->orderBy);

        $coordinations = [];

        foreach ($this->coordinationRepository->findAll(activeOnly: false) as $coordination) {
            $coordinations[$coordination->id()] = new CoordinationDataDTO(
                id: $coordination->id(),
                code: $coordination->code(),
                name: $coordination->name(),
            );
        }

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
                coordinationId: $user->coordinationId(),
                coordination: $user->coordinationId() === null ? null : ($coordinations[$user->coordinationId()] ?? null),
            );
        }

        return new ListAllUserOutputDTO(users: $usersData);
    }
}
