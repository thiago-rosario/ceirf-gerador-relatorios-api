<?php

namespace Database\Seeders\Sizes;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sizes')->upsert([
            ['name' => '1B'],
            ['name' => '1A'],
            ['name' => '1'],
            ['name' => 'Sem Padrão'],
        ], ['name'], ['name']);
    }
}
