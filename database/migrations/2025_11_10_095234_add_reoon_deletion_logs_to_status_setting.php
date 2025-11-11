<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('status_settings', function (Blueprint $table) {
            $table->boolean('reoon_deletion_status')->default('1')->after('invoice_deletion_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_settings', function (Blueprint $table) {
            $table->dropColumn('reoon_deletion_status');
        });
    }
};
