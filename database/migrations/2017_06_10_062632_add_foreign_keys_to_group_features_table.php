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
        Schema::table('group_features', function (Blueprint $table): void {
            // Get all indexes for the group_features table
            $indexes = DB::select('SHOW INDEX FROM group_features');

            // Check if the specific foreign key already exists
            $foreignKeyExists = false;
            foreach ($indexes as $index) {
                if ($index->Key_name === 'group_features_group_id_foreign') {
                    $foreignKeyExists = true;
                    break;
                }
            }

            // If the foreign key does not exist, add it
            if (! $foreignKeyExists) {
                $table->foreign('group_id')
                    ->references('id')
                    ->on('product_groups')
                    ->onUpdate('RESTRICT')
                    ->onDelete('RESTRICT');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_features', function (Blueprint $table): void {
            $table->dropForeign('group_features_group_id_foreign');
        });
    }
};
