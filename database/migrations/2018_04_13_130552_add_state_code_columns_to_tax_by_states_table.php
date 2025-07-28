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
        if (! Schema::hasColumn('tax_by_states', 'state_code')) {
            Schema::table('tax_by_states', function (Blueprint $table) {
                $table->string('state_code');
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
        Schema::table('tax_by_states', function (Blueprint $table) {
            $table->dropColumn('state_code');
        });
    }
};
