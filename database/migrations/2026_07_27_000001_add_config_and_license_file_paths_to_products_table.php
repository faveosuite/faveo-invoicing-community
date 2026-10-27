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
        Schema::table('products', function (Blueprint $table): void {
            // Where inside a shared-build zip to write this product's own
            // identity/config content and signed license file. Null = don't
            // write one. Replaces guessing the zip's shape (config.php vs
            // storage/*.ini) or branching on product_type — this system also
            // distributes third-party products whose internal layout
            // billing doesn't control.
            $table->string('config_file_path')->nullable()->after('product_key');
            $table->string('license_file_path')->nullable()->after('config_file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['config_file_path', 'license_file_path']);
        });
    }
};
