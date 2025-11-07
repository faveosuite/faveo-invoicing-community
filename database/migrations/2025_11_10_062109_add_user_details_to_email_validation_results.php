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
        Schema::table('email_validation_results', function (Blueprint $table) {
            $table->string('state')->nullable();
            $table->string('town')->nullable();
            $table->string('mobile')->nullable();
            $table->string('mobile_code')->nullable();
            $table->string('mobile_country_iso')->nullable();
            $table->string('country')->nullable();
            $table->string('company')->nullable();
            $table->string('address')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('timezone_id')->nullable();
            $table->string('registration')->nullable();
            $table->string('ip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_validation_results', function (Blueprint $table) {
            $table->dropColumn('state');
            $table->dropColumn('town');
            $table->dropColumn('mobile');
            $table->dropColumn('mobile_code');
            $table->dropColumn('mobile_country_iso');
            $table->dropColumn('country');
            $table->dropColumn('company');
            $table->dropColumn('address');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('timezone_id');
            $table->dropColumn('ip');
        });
    }
};
