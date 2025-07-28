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
        Schema::table('product_uploads', function (Blueprint $table) {
            if (! Schema::hasColumn('product_uploads', 'is_private')) {
                $table->boolean('is_private')->default(0);
            }
            if (! Schema::hasColumn('product_uploads', 'is_restricted')) {
                $table->boolean('is_restricted')->default(0);
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
        Schema::table('product_uploads', function (Blueprint $table) {
            $table->dropColumn('is_private');
            $table->dropColumn('is_restricted');
        });
    }
};
