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
            $table->unsignedBigInteger('msg91_third_party_id')->nullable()->after('msg91_template_id');

            $table->foreign('msg91_third_party_id')
                ->references('id')
                ->on('third_party_apps')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropForeign(['msg91_third_party_id']);
            $table->dropColumn('msg91_third_party_id');
        });
    }
};
