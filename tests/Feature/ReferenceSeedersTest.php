<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('default seeding installs the reference data and can be repeated without changing IDs', function () {
    $expectedRecords = [
        'roles' => [
            ['code' => 'report_author', 'name' => 'Autor de relatório'],
            ['code' => 'reviewer', 'name' => 'Revisor'],
            ['code' => 'super_user', 'name' => 'Superusuário'],
            ['code' => 'viewer', 'name' => 'Visualizador'],
        ],
        'forces' => [
            ['code' => 'PM', 'name' => 'Policia Militar', 'is_active' => true],
            ['code' => 'PC', 'name' => 'Policia Civil', 'is_active' => true],
            ['code' => 'BM', 'name' => 'Bombeiro militar', 'is_active' => true],
            ['code' => 'DPT', 'name' => 'Departamento de policia tecnica', 'is_active' => true],
        ],
        'sizes' => [
            ['name' => '1B'], ['name' => '1A'], ['name' => '1'], ['name' => 'Sem Padrão'],
        ],
        'coordinations' => [
            ['code' => 'COTEC', 'name' => 'coordenação tecnica', 'is_active' => true],
            ['code' => 'COPROJ', 'name' => 'coordenação de projetos', 'is_active' => true],
            ['code' => 'CORMAN', 'name' => 'Coordenação de manutenção', 'is_active' => true],
            ['code' => 'CADM', 'name' => 'Coordenação Administrativa', 'is_active' => true],
            ['code' => 'COROB', 'name' => 'Coordenação de obras', 'is_active' => true],
        ],
    ];
    $this->seed(DatabaseSeeder::class);
    $originalIds = [];
    foreach ($expectedRecords as $table => $records) {
        $originalIds[$table] = DB::table($table)->orderBy('id')->pluck('id')->all();
    }

    $this->seed(DatabaseSeeder::class);

    foreach ($expectedRecords as $table => $records) {
        $this->assertDatabaseCount($table, count($records));
        foreach ($records as $record) {
            $this->assertDatabaseHas($table, $record);
        }
        expect(DB::table($table)->orderBy('id')->pluck('id')->all())->toBe($originalIds[$table]);
        expect(Schema::hasColumn($table, 'created_at'))->toBeFalse();
        expect(Schema::hasColumn($table, 'updated_at'))->toBeFalse();
    }
    $this->assertDatabaseEmpty('users');
    $this->assertDatabaseEmpty('user_roles');
});

test('seeding restores predefined names and active flags while preserving IDs and descriptions', function () {
    $forceId = DB::table('forces')->insertGetId(['code' => 'PM', 'name' => 'Old force', 'is_active' => false]);
    $roleId = DB::table('roles')->insertGetId(['code' => 'reviewer', 'name' => 'Old role']);
    $coordinationId = DB::table('coordinations')->insertGetId([
        'code' => 'COTEC', 'name' => 'Old coordination', 'is_active' => false, 'description' => 'Existing description',
    ]);

    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseHas('forces', ['id' => $forceId, 'code' => 'PM', 'name' => 'Policia Militar', 'is_active' => true]);
    $this->assertDatabaseHas('roles', ['id' => $roleId, 'code' => 'reviewer', 'name' => 'Revisor']);
    $this->assertDatabaseHas('coordinations', [
        'id' => $coordinationId, 'code' => 'COTEC', 'name' => 'coordenação tecnica',
        'is_active' => true, 'description' => 'Existing description',
    ]);
});

test('default seeding installs 27 active identity territories without duplicating existing records', function () {
    $territoryId = DB::table('identity_territories')->insertGetId([
        'name' => 'Médio Sudoeste da Bahia', 'is_active' => false,
    ]);

    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('identity_territories', 27);
    $this->assertDatabaseHas('identity_territories', [
        'id' => $territoryId, 'name' => 'Médio Sudoeste da Bahia', 'is_active' => true,
    ]);
    $this->assertDatabaseMissing('identity_territories', ['is_active' => false]);
    $this->assertDatabaseMissing('identity_territories', ['name' => 'Itapetinga']);
    foreach ([
        'Litoral Norte e Agreste Baiano',
        'Médio Rio de Contas',
        'Metropolitano de Salvador',
        'Piemonte Norte do Itapicuru',
        'Semiárido Nordeste II',
        'Velho Chico',
    ] as $name) {
        $this->assertDatabaseHas('identity_territories', ['name' => $name]);
    }
});
