<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * License reports table (from afl_reports).
     * Audit/piracy reports from client installations.
     */
    public function up(): void
    {
        Schema::create('license_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('license_code')->nullable()->index();
            $table->timestamp('report_date_time')->useCurrent();
            $table->text('report_text')->nullable();
            $table->string('report_system')->nullable();
            $table->string('report_status')->default('pending');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['report_status', 'report_date_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_reports');
    }
};
