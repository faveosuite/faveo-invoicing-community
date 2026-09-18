<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_uploads', function (Blueprint $table): void {
            $table->timestamp('version_expire_date')->nullable()->after('file');
            $table->mediumInteger('version_install_count')->default(0)->after('version_expire_date');
            $table->tinyInteger('status')->default(1)->after('version_install_count');
        });
    }

    public function down(): void
    {
        Schema::table('product_uploads', function (Blueprint $table): void {
            $table->dropColumn(['version_expire_date', 'version_install_count', 'status']);
        });
    }
};
