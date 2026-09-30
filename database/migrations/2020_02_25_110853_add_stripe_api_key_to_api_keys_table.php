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
            if (! Schema::hasColumn('api_keys', 'stripe_key')) {
                $table->string('stripe_key')->nullable();
            }

            if (! Schema::hasColumn('api_keys', 'stripe_secret')) {
                $table->string('stripe_secret')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table): void {
            $table->dropColumn('stripe_key');
            $table->dropColumn('stripe_secret');
        });
    }
};
