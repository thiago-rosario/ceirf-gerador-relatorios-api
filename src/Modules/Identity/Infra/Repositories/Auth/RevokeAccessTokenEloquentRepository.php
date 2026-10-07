<?php

declare(strict_types=1);

namespace src\Modules\Identity\Infra\Repositories\Auth;

use Illuminate\Support\Facades\DB;

class RevokeAccessTokenEloquentRepository
{
    public function revokeAccessToken(string $accessToken): void
    {
        DB::table('user_access_tokens')->where('token', hash('sha256', $accessToken))->delete();
    }
}
