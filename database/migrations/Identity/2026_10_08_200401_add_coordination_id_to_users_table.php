<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedSmallInteger('coordination_id')->nullable();
            $table->foreign('coordination_id', 'fk_coordinations_users')->references('id')->on('coordinations')->noActionOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign('fk_coordinations_users')->set('columns', ['coordination_id']);
            $table->dropColumn('coordination_id');
        });
    }
};
