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
            if (! Schema::hasColumn('settings', 'default_currency')) {
                $table->string('default_currency', 255)->nullable();
            }

            if (! Schema::hasColumn('settings', 'default_symbol')) {
                $table->string('default_symbol', 255)->nullable();
            }

            if (! Schema::hasColumn('settings', 'file_storage')) {
                $table->string('file_storage', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn('file_storage');
            $table->dropColumn('default_symbol');
            $table->dropColumn('default_currency');
        });
    }
};
