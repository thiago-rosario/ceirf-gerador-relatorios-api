<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Repositories\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use src\Modules\Identity\Domain\Entity\UserEntity;
use src\Modules\Identity\Model\User as UserModel;

class CreateAccessTokenEloquentRepository
{
    public function createAccessToken(UserEntity $user): string
    {
        $model = UserModel::query()->where('uuid', $user->id()->value())->firstOrFail();
        $accessToken = Str::random(64);

        DB::table('user_access_tokens')->insert([
            'user_id' => $model->id,
            'token' => hash('sha256', $accessToken),
            'created_at' => now(),
        ]);

        return $accessToken;
    }
}
