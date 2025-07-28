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
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile_country_iso', 5)->nullable()->after('mobile_code');
        });

        // For existing users, set the mobile_country_iso to the country
        DB::table('users')
            ->whereNotNull('country')
            ->update([
                'mobile_country_iso' => DB::raw('country'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['mobile_country_iso']);
        });
    }
};
