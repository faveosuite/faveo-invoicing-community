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
        Schema::table('zoho_integrations', function (Blueprint $table): void {
            // Zoho Campaigns mailing list picked by the admin; null = the default list from config.
            $table->string('list_key')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zoho_integrations', function (Blueprint $table): void {
            $table->dropColumn('list_key');
        });
    }
};
