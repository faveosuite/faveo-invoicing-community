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
        Schema::table('api_keys', function (Blueprint $table) {
            $table->string('email_verification_mode')->nullable()->after('rzp_secret');
            $table->string('email_verification_key')->nullable()->after('email_verification_mode');
            $table->string('email_verification_provider')->nullable()->after('email_verification_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn(['email_verification_mode']);
            $table->dropColumn(['email_verification_key']);
            $table->dropColumn(['email_verification_provider']);
        });
    }
};
