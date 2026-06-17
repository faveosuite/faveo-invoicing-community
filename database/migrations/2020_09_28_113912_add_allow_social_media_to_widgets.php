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
        if (! Schema::hasColumn('widgets', 'allow_social_media')) {
            Schema::table('widgets', function (Blueprint $table): void {
                $table->boolean('allow_social_media')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('widgets', function (Blueprint $table): void {
            $table->dropColumn('allow_social_media');
        });
    }
};
