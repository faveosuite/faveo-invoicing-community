<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            Schema::table('configurable_options', function (Blueprint $table) {
                $table->foreign('group_id')
                    ->references('id')
                    ->on('product_groups')
                    ->onUpdate('RESTRICT')
                    ->onDelete('RESTRICT');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurable_options', function (Blueprint $table) {
            $table->dropForeign('configurable_options_group_id_foreign');
        });
    }
};
