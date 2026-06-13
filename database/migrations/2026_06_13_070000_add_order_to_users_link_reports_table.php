<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users_link_reports', 'order')) {
            Schema::table('users_link_reports', function (Blueprint $table) {
                $table->unsignedInteger('order')->default(0)->after('column_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users_link_reports', 'order')) {
            Schema::table('users_link_reports', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }
};
