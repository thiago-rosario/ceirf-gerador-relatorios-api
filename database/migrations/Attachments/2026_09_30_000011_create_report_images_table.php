<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_images', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('report_id');
            $table->unsignedBigInteger('uploaded_by');
            $table->string('type', 50);
            $table->string('original_name', 255);
            $table->string('storage_path', 500);
            $table->string('mime_type', 100);
            $table->bigInteger('size_bytes');
            $table->string('file_hash', 64);
            $table->string('caption', 500)->nullable();
            $table->smallInteger('position')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
            $table->unique(['report_id', 'file_hash'], 'uq_report_images_report_hash');
            $table->index('report_id', 'idx_report_images_report');
            $table->foreign('report_id', 'fk_reports_report_images')->references('id')->on('reports')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('uploaded_by', 'fk_users_report_images')->references('id')->on('users')->noActionOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_images');
    }
};
