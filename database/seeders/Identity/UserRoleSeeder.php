<?php

namespace Database\Seeders\Identity;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->upsert([
            ['code' => 'operator', 'name' => 'Operador'],
            ['code' => 'reviewer', 'name' => 'Revisor'],
            ['code' => 'super_user', 'name' => 'Superusuário'],
            ['code' => 'viewer', 'name' => 'Visualizador'],
        ], ['code'], ['name']);
    }
}
