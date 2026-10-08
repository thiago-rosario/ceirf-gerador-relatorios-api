<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Repositories\Queries;

use src\Modules\Identity\Domain\Entity\RoleEntity;
use src\Modules\Identity\Model\Role as RoleModel;

class GetRolesEloquentRepository
{
    /**
     * @return list<RoleEntity>
     */
    public function getRoles(): array
    {
        $roles = RoleModel::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->map(fn (RoleModel $model): RoleEntity => new RoleEntity(
                id: $model->id,
                code: $model->code,
                name: $model->name,
                role: $model->toUserRole(),
            ))
            ->all();

        return array_values($roles);
    }
}
