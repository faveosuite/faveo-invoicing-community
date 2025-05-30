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
        Schema::table('email_mobile_validation_providers', function (Blueprint $table) {
            $table->boolean('to_use')->default(false)->after('accepted_output');
            $table->string('type')->nullable()->after('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_mobile_validation_providers', function (Blueprint $table) {
            $table->dropColumn('to_use');
            $table->dropColumn('type');
        });
    }
};
