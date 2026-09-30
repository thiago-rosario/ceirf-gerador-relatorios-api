<?php

namespace Database\Seeders\Identity;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->upsert([
            ['code' => 'report_author', 'name' => 'Autor de relatório'],
            ['code' => 'reviewer', 'name' => 'Revisor'],
            ['code' => 'super_user', 'name' => 'Superusuário'],
            ['code' => 'viewer', 'name' => 'Visualizador'],
        ], ['code'], ['name']);
    }
}
