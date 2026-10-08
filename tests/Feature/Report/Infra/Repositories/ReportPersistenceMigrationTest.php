<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use src\Modules\Identity\Model\User;
use src\Modules\Report\Model\Report;

test('upgrades existing report families and uploads without changing their relational identities', function (): void {
    $migration = require database_path('migrations/Report/2026_10_08_123659_add_persistence_fields_to_reports.php');
    $migration->down();
    $user = User::factory()->create();
    $coordinationId = DB::table('coordinations')->insertGetId(['code' => 'COTEC', 'name' => 'Tecnica']);
    $typeId = DB::table('report_types')->insertGetId(['coordination_id' => $coordinationId, 'code' => 'inspection', 'name' => 'Inspection']);
    $seriesId = DB::table('report_series')->insertGetId(['created_by' => $user->id, 'created_at' => '2020-01-01 08:00:00']);
    $attributes = [
        'report_series_id' => $seriesId,
        'report_type_id' => $typeId,
        'created_by' => $user->id,
        'created_at' => '2020-01-01 08:00:00',
    ];
    $rootId = DB::table('reports')->insertGetId($attributes);
    $revisionId = DB::table('reports')->insertGetId([...$attributes, 'revision_number' => 1, 'previous_report_id' => $rootId]);
    $image = [
        'report_id' => $rootId,
        'uploaded_by' => $user->id,
        'type' => 'location_map',
        'original_name' => 'map.png',
        'storage_path' => 'reports/map.png',
        'mime_type' => 'image/png',
        'size_bytes' => 1024,
        'file_hash' => hash('sha256', 'map'),
        'position' => 1,
        'created_at' => '2020-01-01 08:00:00',
    ];
    $imageId = DB::table('report_images')->insertGetId($image);

    /** SQLite rebuilds referenced tables inside RefreshDatabase's transaction. */
    DB::statement('PRAGMA defer_foreign_keys = ON');
    $migration->up();

    $rootUuid = DB::table('reports')->where('id', $rootId)->value('uuid');
    $revisionUuid = DB::table('reports')->where('id', $revisionId)->value('uuid');
    expect(Str::isUuid($rootUuid))->toBeTrue();
    expect(Str::isUuid($revisionUuid))->toBeTrue();
    expect($revisionUuid)->not->toBe($rootUuid);
    expect(Str::isUuid(DB::table('report_images')->where('id', $imageId)->value('uuid')))->toBeTrue();
    $this->assertDatabaseHas('report_series', ['id' => $seriesId, 'uuid' => $rootUuid, 'created_by' => $user->id]);
    $this->assertDatabaseHas('reports', ['id' => $rootId, ...$attributes, 'status' => 'DRAFT']);
    $this->assertDatabaseHas('reports', ['id' => $revisionId, 'previous_report_id' => $rootId, 'report_series_id' => $seriesId]);
    $this->assertDatabaseHas('report_images', ['id' => $imageId, ...$image]);

    $migration->down();

    expect(Schema::hasColumn('reports', 'uuid'))->toBeFalse();
    expect(Schema::hasColumn('reports', 'payload'))->toBeFalse();
    $this->assertDatabaseHas('reports', ['id' => $rootId, ...$attributes]);
    $this->assertDatabaseHas('reports', ['id' => $revisionId, 'previous_report_id' => $rootId]);
    $this->assertDatabaseHas('report_images', ['id' => $imageId, ...$image]);
});

test('refuses an incompatible rollback before changing reports without a type', function (): void {
    $report = Report::factory()->create();
    $migration = require database_path('migrations/Report/2026_10_08_123659_add_persistence_fields_to_reports.php');

    expect(fn () => $migration->down())->toThrow(LogicException::class);

    expect(Schema::hasColumn('reports', 'uuid'))->toBeTrue();
    expect(Schema::hasColumn('reports', 'payload'))->toBeTrue();
    $this->assertModelExists($report);
    expect($report->fresh()?->payload)->toBe($report->payload);
});
