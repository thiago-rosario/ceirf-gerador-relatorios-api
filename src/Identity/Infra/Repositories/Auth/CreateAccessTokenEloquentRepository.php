<?php

declare(strict_types=1);

namespace src\Identity\Infra\Repositories\Auth;

use App\Models\User as UserModel;
use src\Identity\Domain\Entity\UserEntity;

class CreateAccessTokenEloquentRepository
{
    public function createAccessToken(UserEntity $user): string
    {
        $model = UserModel::query()->findOrFail($user->id()->value());

        return $model->createToken('access_token')->plainTextToken;
    }
}
