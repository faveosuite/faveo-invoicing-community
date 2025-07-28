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
        if (! Schema::hasColumn('auto_renewals', 'invoice_number')) {
            Schema::table('auto_renewals', function (Blueprint $table) {
                $table->string('invoice_number')->nullable();
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
        Schema::table('auto_renewals', function (Blueprint $table) {
            $table->dropColumn('invoice_number');
        });
    }
};
