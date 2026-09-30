<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('report_series_id');
            $table->unsignedSmallInteger('report_type_id');
            $table->unsignedBigInteger('previous_report_id')->nullable();
            $table->smallInteger('revision_number')->default(0);
            $table->unsignedInteger('municipality_id')->nullable();
            $table->unsignedSmallInteger('force_id')->nullable();
            $table->unsignedSmallInteger('size_id')->nullable();
            $table->string('typology', 100)->nullable();
            $table->string('sei_number', 80)->nullable();
            $table->date('inspection_date')->nullable();
            $table->string('present_collaborators', 1000)->nullable();
            $table->text('objective')->nullable();
            $table->string('land_address', 255)->nullable();
            $table->string('land_zip_code', 10)->nullable();
            $table->string('google_maps_url', 500)->nullable();
            $table->decimal('distance_from_ssp_km', 10, 2)->nullable();
            $table->text('descriptive_memorial')->nullable();
            $table->decimal('land_area_m2', 12, 2)->nullable();
            $table->decimal('land_perimeter_m', 12, 2)->nullable();
            $table->text('descriptive_memorial_notes')->nullable();
            $table->string('topography', 255)->nullable();
            $table->string('soil_type', 255)->nullable();
            $table->string('drainage', 255)->nullable();
            $table->string('existing_vegetation', 255)->nullable();
            $table->smallInteger('infrastructure_water_network')->nullable();
            $table->smallInteger('infrastructure_high_voltage_network')->nullable();
            $table->smallInteger('infrastructure_low_voltage_network')->nullable();
            $table->smallInteger('infrastructure_sewage_network')->nullable();
            $table->smallInteger('infrastructure_telephony')->nullable();
            $table->smallInteger('infrastructure_public_lighting')->nullable();
            $table->smallInteger('infrastructure_internet')->nullable();
            $table->smallInteger('infrastructure_waste_collection')->nullable();
            $table->smallInteger('infrastructure_paving')->nullable();
            $table->smallInteger('infrastructure_existing_buildings')->nullable();
            $table->text('existing_structures_condition')->nullable();
            $table->text('conclusion')->nullable();
            $table->smallInteger('checklist_sei_construction_request')->nullable();
            $table->smallInteger('checklist_sei_land_and_typology_identification')->nullable();
            $table->smallInteger('checklist_state_owned_land')->nullable();
            $table->smallInteger('checklist_simov_legalized')->nullable();
            $table->smallInteger('checklist_compatible_dimensions')->nullable();
            $table->smallInteger('checklist_slope_or_level_risk')->nullable();
            $table->smallInteger('checklist_stormwater_drainage')->nullable();
            $table->smallInteger('checklist_flood_history')->nullable();
            $table->smallInteger('checklist_electricity_supply')->nullable();
            $table->smallInteger('checklist_water_supply')->nullable();
            $table->smallInteger('checklist_sewage_supply')->nullable();
            $table->smallInteger('checklist_paving_and_sidewalk')->nullable();
            $table->smallInteger('checklist_regular_waste_collection')->nullable();
            $table->smallInteger('checklist_demolition_required')->nullable();
            $table->smallInteger('checklist_easy_public_access')->nullable();
            $table->smallInteger('checklist_domain_strip_or_non_buildable_area')->nullable();
            $table->smallInteger('checklist_technical_feasibility_report')->nullable();
            $table->smallInteger('checklist_report_attached_to_sei')->nullable();
            $table->smallInteger('checklist_works_dashboard_updated')->nullable();
            $table->smallInteger('checklist_environmental_protection_area')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
            $table->unique(['report_series_id', 'revision_number'], 'uq_reports_series_revision');
            $table->index('created_at', 'idx_reports_created_at');
            $table->index('updated_at', 'idx_reports_updated_at');
            $table->index('report_type_id', 'idx_reports_report_type');
            $table->index('municipality_id', 'idx_reports_municipality');
            $table->foreign('report_series_id', 'fk_report_series_reports')->references('id')->on('report_series')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('report_type_id', 'fk_report_types_reports')->references('id')->on('report_types')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('created_by', 'fk_users_reports')->references('id')->on('users')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('previous_report_id', 'fk_reports_previous_report')->references('id')->on('reports')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('municipality_id', 'fk_municipalities_reports')->references('id')->on('municipalities')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('force_id', 'fk_forces_reports')->references('id')->on('forces')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('size_id', 'fk_sizes_reports')->references('id')->on('sizes')->noActionOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
