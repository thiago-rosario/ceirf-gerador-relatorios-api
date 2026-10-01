<?php

namespace Database\Seeders\Coordinations;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoordinationsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('coordinations')->upsert([
            ['code' => 'COTEC', 'name' => 'coordenação tecnica', 'is_active' => true],
            ['code' => 'COPROJ', 'name' => 'coordenação de projetos', 'is_active' => true],
            ['code' => 'CORMAN', 'name' => 'Coordenação de manutenção', 'is_active' => true],
            ['code' => 'CADM', 'name' => 'Coordenação Administrativa', 'is_active' => true],
            ['code' => 'COROB', 'name' => 'Coordenação de obras', 'is_active' => true],
        ], ['code'], ['name', 'is_active']);
    }
}
