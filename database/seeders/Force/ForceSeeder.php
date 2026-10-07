<?php

namespace Database\Seeders\Force;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ForceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('forces')->insertOrIgnore([
            ['code' => 'PM', 'name' => 'Policia Militar', 'is_active' => true],
            ['code' => 'PC', 'name' => 'Policia Civil', 'is_active' => true],
            ['code' => 'BM', 'name' => 'Bombeiro militar', 'is_active' => true],
            ['code' => 'DPT', 'name' => 'Departamento de policia tecnica', 'is_active' => true],
            ['code' => 'PC-PM', 'name' => 'Conjugada', 'is_active' => true],
            ['code' => 'SSP', 'name' => 'Secretaria da Segurança Pública', 'is_active' => true],
        ]);
    }
}
