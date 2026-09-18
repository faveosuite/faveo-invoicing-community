<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all indexes for the configurable_options table
        $indexes = DB::select('SHOW INDEX FROM configurable_options');

        // Check if the specific foreign key already exists
        $foreignKeyExists = false;
        foreach ($indexes as $index) {
            if ($index->Key_name === 'configurable_options_group_id_foreign') {
                $foreignKeyExists = true;
                break;
            }
        }

        // If the foreign key does not exist, add it
        if (! $foreignKeyExists) {
            Schema::table('configurable_options', function (Blueprint $table): void {
                $table->foreign('group_id')
                    ->references('id')
                    ->on('product_groups')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configurable_options', function (Blueprint $table): void {
            $table->dropForeign('configurable_options_group_id_foreign');
        });
    }
};
