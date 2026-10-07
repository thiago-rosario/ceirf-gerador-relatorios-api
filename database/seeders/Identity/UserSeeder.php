<?php

declare(strict_types=1);

namespace Database\Seeders\Identity;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use src\Modules\Identity\Domain\Enum\UserRoleEnum;
use src\Modules\Identity\Model\Role;
use src\Modules\Identity\Model\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        DB::transaction(function (): void {
            $users = [
                ['name' => 'Operador', 'email' => 'operador@ceirf.test', 'role' => UserRoleEnum::OPERATOR],
                ['name' => 'Visualizador', 'email' => 'viewer@ceirf.test', 'role' => UserRoleEnum::VIEWER],
                ['name' => 'Revisor', 'email' => 'reviewer@ceirf.test', 'role' => UserRoleEnum::REVIEWER],
                ['name' => 'Superusuário', 'email' => 'superusuario@ceirf.test', 'role' => UserRoleEnum::SUPERUSER],
            ];

            foreach ($users as $data) {
                $user = User::query()->updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'password' => 'senha',
                        'is_active' => true,
                        'must_change_password' => false,
                    ],
                );

                $user->roles()->sync([Role::forRole($data['role'])->id]);
            }
        });
    }
}
