<?php

namespace Database\Seeders\Locations;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IdentityTerritorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('identity_territories')->upsert([
            ['name' => 'Bacia do Jacuípe', 'is_active' => true],
            ['name' => 'Bacia do Paramirim', 'is_active' => true],
            ['name' => 'Bacia do Rio Corrente', 'is_active' => true],
            ['name' => 'Bacia do Rio Grande', 'is_active' => true],
            ['name' => 'Baixo Sul', 'is_active' => true],
            ['name' => 'Chapada Diamantina', 'is_active' => true],
            ['name' => 'Costa do Descobrimento', 'is_active' => true],
            ['name' => 'Extremo Sul', 'is_active' => true],
            ['name' => 'Irecê', 'is_active' => true],
            ['name' => 'Itaparica', 'is_active' => true],
            ['name' => 'Litoral Sul', 'is_active' => true],
            ['name' => 'Litoral Norte e Agreste Baiano', 'is_active' => true],
            ['name' => 'Médio Rio de Contas', 'is_active' => true],
            ['name' => 'Médio Sudoeste da Bahia', 'is_active' => true],
            ['name' => 'Metropolitano de Salvador', 'is_active' => true],
            ['name' => 'Piemonte da Diamantina', 'is_active' => true],
            ['name' => 'Piemonte do Paraguaçu', 'is_active' => true],
            ['name' => 'Piemonte Norte do Itapicuru', 'is_active' => true],
            ['name' => 'Portal do Sertão', 'is_active' => true],
            ['name' => 'Recôncavo', 'is_active' => true],
            ['name' => 'Semiárido Nordeste II', 'is_active' => true],
            ['name' => 'Sertão do São Francisco', 'is_active' => true],
            ['name' => 'Sertão Produtivo', 'is_active' => true],
            ['name' => 'Sisal', 'is_active' => true],
            ['name' => 'Sudoeste Baiano', 'is_active' => true],
            ['name' => 'Vale do Jiquiriçá', 'is_active' => true],
            ['name' => 'Velho Chico', 'is_active' => true],
        ], ['name'], ['is_active']);
    }
}
