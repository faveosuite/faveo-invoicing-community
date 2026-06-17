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
        Schema::table('api_keys', function (Blueprint $table): void {
            if (! Schema::hasColumn('api_keys', 'msg91_auth_key')) {
                $table->string('msg91_auth_key', 255)->nullable();
            }

            if (! Schema::hasColumn('api_keys', 'msg91_sender')) {
                $table->string('msg91_sender', 50)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table): void {
            $table->dropColumn('msg91_auth_key');
            $table->dropColumn('msg91_sender');
        });
    }
};
