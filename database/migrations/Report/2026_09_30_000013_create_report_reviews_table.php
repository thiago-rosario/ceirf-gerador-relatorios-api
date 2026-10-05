<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_reviews', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('report_id');
            $table->unsignedBigInteger('reviewer_id');
            $table->string('status', 30);
            $table->text('comment')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
            $table->index('report_id', 'idx_report_reviews_report');
            $table->index('reviewer_id', 'idx_report_reviews_reviewer');
            $table->index(['report_id', 'created_at'], 'idx_report_reviews_report_created');
            $table->foreign('report_id', 'fk_reports_report_reviews')->references('id')->on('reports')->noActionOnDelete()->noActionOnUpdate();
            $table->foreign('reviewer_id', 'fk_users_report_reviews')->references('id')->on('users')->noActionOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_reviews');
    }
};
