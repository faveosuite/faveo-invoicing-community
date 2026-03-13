<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('help_support_url', 500)->nullable()->after('knowledge_base_url');
            $table->string('help_docs_url', 500)->nullable()->after('help_support_url');
            $table->string('help_description', 255)->nullable()->after('help_docs_url');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['help_support_url', 'help_docs_url', 'help_description']);
        });
    }
};
