<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique();
            $table->string('status', 30)->default('DRAFT');
            $table->json('payload')->nullable();
            $table->unsignedSmallInteger('report_type_id')->nullable()->change();
        });

        Schema::table('report_series', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique();
        });

        foreach (['reports', 'report_images', 'report_attachments'] as $tableName) {
            if ($tableName !== 'reports') {
                Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                    $table->uuid('uuid')->nullable();
                    $table->unique(['report_id', 'uuid'], 'uq_'.$tableName.'_report_uuid');
                });
            }

            DB::table($tableName)->whereNull('uuid')->chunkById(100, function (Collection $rows) use ($tableName): void {
                foreach ($rows as $row) {
                    DB::table($tableName)->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
                }
            });
        }

        DB::table('report_series')->whereNull('uuid')->chunkById(100, function (Collection $rows): void {
            foreach ($rows as $row) {
                $rootUuid = DB::table('reports')->where('report_series_id', $row->id)->where('revision_number', 0)->value('uuid');
                DB::table('report_series')->where('id', $row->id)->update(['uuid' => $rootUuid ?? (string) Str::uuid()]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('reports')->whereNull('report_type_id')->exists()) {
            throw new LogicException('Defina o tipo dos relatórios antes de reverter a persistência.');
        }

        foreach (['report_images', 'report_attachments'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $table->dropUnique('uq_'.$tableName.'_report_uuid');
                $table->dropColumn('uuid');
            });
        }

        Schema::table('report_series', function (Blueprint $table): void {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });

        Schema::table('reports', function (Blueprint $table): void {
            $table->dropUnique(['uuid']);
            $table->dropColumn(['uuid', 'status', 'payload']);
            $table->unsignedSmallInteger('report_type_id')->nullable(false)->change();
        });
    }
};
