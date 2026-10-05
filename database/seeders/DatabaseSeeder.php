<?php

namespace Database\Seeders;

use Database\Seeders\Coordinations\CoordinationsSeeder;
use Database\Seeders\Force\ForceSeeder;
use Database\Seeders\Identity\UserRoleSeeder;
use Database\Seeders\Identity\UserSeeder;
use Database\Seeders\Locations\IdentityTerritorySeeder;
use Database\Seeders\Sizes\SizeSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserRoleSeeder::class,
            UserSeeder::class,
            ForceSeeder::class,
            SizeSeeder::class,
            CoordinationsSeeder::class,
            IdentityTerritorySeeder::class,
        ]);
    }
}
