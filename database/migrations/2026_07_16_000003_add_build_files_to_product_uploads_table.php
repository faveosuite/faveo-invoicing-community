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
        Schema::table('product_uploads', function (Blueprint $table): void {
            // Map of build_type => filename (e.g. {"obfuscated": "helpdesk-v1.2.0.zip",
            // "source": "helpdesk-source-v1.2.0.zip"}), saved together on the same
            // upload so a product's build_type can change later and the next
            // download picks the matching entry — not hard-coded to exactly two
            // variants, so a future third build_type needs no schema change.
            $table->json('build_files')->nullable()->after('file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_uploads', function (Blueprint $table): void {
            $table->dropColumn('build_files');
        });
    }
};
