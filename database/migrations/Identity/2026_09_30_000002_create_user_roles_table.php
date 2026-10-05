<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedSmallInteger('role_id');
            $table->primary(['user_id', 'role_id']);
            $table->foreign('user_id', 'fk_users_user_roles')->references('id')->on('users')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('role_id', 'fk_roles_user_roles')->references('id')->on('roles')->noActionOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
