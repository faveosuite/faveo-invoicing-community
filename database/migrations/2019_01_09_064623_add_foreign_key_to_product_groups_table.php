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
        Schema::table('product_groups', function (Blueprint $table) {
            if (! Schema::hasColumn('product_groups', 'pricing_templates_id')) {
                $table->unsignedInteger('pricing_templates_id');
                $table->foreign('pricing_templates_id')->references('id')->on('pricing_templates');
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
        Schema::table('product_groups', function (Blueprint $table) {
            $table->dropColumn('pricing_templates_id');
            $table->dropForeign('pricing_templates');
        });
    }
};
