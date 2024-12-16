<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_features', function (Blueprint $table) {
            // Get all indexes for the group_features table
            $indexes = DB::select("SHOW INDEX FROM group_features");

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
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_features', function (Blueprint $table) {
            $table->dropForeign('group_features_group_id_foreign');
        });
    }
};
