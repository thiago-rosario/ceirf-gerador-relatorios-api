<?php

declare(strict_types=1);

namespace src\Modules\Identity\Application\Usecase\User;

use src\Modules\Identity\Application\DTO\User\GetRolesOutputDTO;
use src\Modules\Identity\Application\DTO\User\RoleDataDTO;
use src\Modules\Identity\Application\Interfaces\Usecase\User\GetRolesUsecaseInterface;
use src\Modules\Identity\Domain\Repository\UserRepositoryInterface;

class GetRolesUsecase implements GetRolesUsecaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function __invoke(): GetRolesOutputDTO
    {
        $roles = $this->repository->getRoles();

        $rolesData = [];

        foreach ($roles as $role) {
            $rolesData[] = new RoleDataDTO(
                id: $role->id(),
                code: $role->code(),
                name: $role->name(),
                role: $role->role()->value,
            );
        }

        return new GetRolesOutputDTO(roles: $rolesData);
    }
}
