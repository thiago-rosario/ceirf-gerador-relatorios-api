<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipalities', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedSmallInteger('identity_territory_id');
            $table->string('name', 100);
            $table->string('state_code', 2)->default('BA');
            $table->boolean('is_active')->default(true);
            $table->unique(['name', 'state_code'], 'uq_municipalities_name_state');
            $table->foreign('identity_territory_id', 'fk_identity_territories_municipalities')->references('id')->on('identity_territories')->noActionOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipalities');
    }
};
