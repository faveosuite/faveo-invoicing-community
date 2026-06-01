<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tax classes become slug-based (WooCommerce model). A product is assigned a
 * class; rates belong to a class; the two join on the slug. The standard class
 * uses an empty slug to match rates with tax_class = ''.
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tax_classes') && ! Schema::hasColumn('tax_classes', 'slug')) {
            Schema::table('tax_classes', function (Blueprint $table) {
                $table->string('slug', 200)->default('')->after('name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tax_classes', 'slug')) {
            Schema::table('tax_classes', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
