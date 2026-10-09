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
        Schema::table('settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('settings', 'cin_no')) {
                $table->string('cin_no')->nullable();
            }

            if (! Schema::hasColumn('settings', 'gstin')) {
                $table->string('gstin')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn('cin_no');
            $table->dropColumn('gstin');
        });
    }
};
