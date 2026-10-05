<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Commands;

use App\Model\Role;
use App\Model\User as UserModel;
use Illuminate\Support\Facades\DB;
use src\Identity\Domain\Entity\UserEntity;

class UpdateUserEloquentRepository
{
    public function update(UserEntity $user): UserEntity
    {
        return DB::transaction(function () use ($user): UserEntity {
            $model = UserModel::query()->with('roles')->where('uuid', $user->id()->value())->firstOrFail();
            $previousRole = $model->role;

            $model->fill([
                'email' => $user->email()->value(),
                'name' => $user->name(),
                'password' => $user->password(),
                'is_active' => $user->isActive(),
                'must_change_password' => $user->mustChangePassword(),
                'updated_at' => $user->updatedAt(),
            ]);

            $passwordWasChanged = $model->isDirty('password');
            $model->save();
            if ($previousRole !== $user->role()) {
                $model->roles()->sync([Role::forRole($user->role())->id]);
            }

            if ($passwordWasChanged || ! $user->isActive()) {
                DB::table('user_access_tokens')->where('user_id', $model->id)->delete();
            }

            return UserEntity::fromModel($model->load('roles'));
        });
    }
}
