<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * @return array{report_series_id: int, report_type_id: int, created_by: int, created_at: string}
 */
function reportMigrationAttributes(): array
{
    $userId = DB::table('users')->insertGetId([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => 'test-password',
        'created_at' => '2026-09-30 12:00:00',
    ]);
    $coordinationId = DB::table('coordinations')->insertGetId([
        'code' => 'CEIRF', 'name' => 'Coordination',
    ]);
    $reportTypeId = DB::table('report_types')->insertGetId([
        'coordination_id' => $coordinationId, 'code' => 'inspection', 'name' => 'Inspection',
    ]);
    $seriesId = DB::table('report_series')->insertGetId([
        'created_by' => $userId, 'created_at' => '2026-09-30 12:00:00',
    ]);

    return [
        'report_series_id' => $seriesId,
        'report_type_id' => $reportTypeId,
        'created_by' => $userId,
        'created_at' => '2026-09-30 12:00:00',
    ];
}

test('reports can be created with only required fields and revised in the same series', function () {
    $attributes = reportMigrationAttributes();
    $reportId = DB::table('reports')->insertGetId($attributes);

    DB::table('reports')->insert([
        ...$attributes,
        'previous_report_id' => $reportId,
        'revision_number' => 1,
    ]);

    $this->assertDatabaseHas('reports', [
        'id' => $reportId, 'revision_number' => 0, 'municipality_id' => null, 'updated_at' => null,
    ]);
    $this->assertDatabaseHas('reports', ['previous_report_id' => $reportId, 'revision_number' => 1]);
    $this->assertDatabaseHas('users', ['id' => $attributes['created_by'], 'is_active' => true]);
});

test('a series rejects duplicate revision numbers', function () {
    $attributes = reportMigrationAttributes();
    DB::table('reports')->insert($attributes);

    expect(fn () => DB::table('reports')->insert($attributes))->toThrow(QueryException::class);

    $this->assertDatabaseCount('reports', 1);
});

test('reports reject references to missing records', function (string $column) {
    $attributes = reportMigrationAttributes();

    expect(fn () => DB::table('reports')->insert([
        ...$attributes, $column => 99999,
    ]))->toThrow(QueryException::class);

    $this->assertDatabaseEmpty('reports');
})->with([
    'series' => 'report_series_id',
    'type' => 'report_type_id',
    'author' => 'created_by',
    'previous revision' => 'previous_report_id',
    'municipality' => 'municipality_id',
    'force' => 'force_id',
    'size' => 'size_id',
]);

test('referenced report records cannot be deleted or have their IDs changed', function (string $table, string $attribute, string $operation) {
    $attributes = reportMigrationAttributes();
    DB::table('reports')->insert($attributes);
    $query = DB::table($table)->where('id', $attributes[$attribute]);

    expect(fn () => $operation === 'delete' ? $query->delete() : $query->update(['id' => 99999]))
        ->toThrow(QueryException::class);

    $this->assertDatabaseHas($table, ['id' => $attributes[$attribute]]);
    $this->assertDatabaseCount('reports', 1);
})->with([
    'delete author' => ['users', 'created_by', 'delete'],
    'update author' => ['users', 'created_by', 'update'],
    'delete series' => ['report_series', 'report_series_id', 'delete'],
    'update series' => ['report_series', 'report_series_id', 'update'],
    'delete type' => ['report_types', 'report_type_id', 'delete'],
    'update type' => ['report_types', 'report_type_id', 'update'],
]);

test('file hashes are unique per report but can be reused in another revision', function (string $table) {
    $attributes = reportMigrationAttributes();
    $reportId = DB::table('reports')->insertGetId($attributes);
    $revisionId = DB::table('reports')->insertGetId([...$attributes, 'revision_number' => 1]);
    $file = [
        'report_id' => $reportId,
        'uploaded_by' => $attributes['created_by'],
        'type' => 'inspection',
        'original_name' => 'inspection.jpg',
        'storage_path' => 'reports/inspection.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 1024,
        'file_hash' => str_repeat('a', 64),
        'created_at' => '2026-09-30 12:00:00',
    ];
    DB::table($table)->insert($file);
    DB::table($table)->insert([...$file, 'report_id' => $revisionId]);

    expect(fn () => DB::table($table)->insert($file))->toThrow(QueryException::class);

    $this->assertDatabaseCount($table, 2);
})->with(['images' => 'report_images', 'attachments' => 'report_attachments']);
