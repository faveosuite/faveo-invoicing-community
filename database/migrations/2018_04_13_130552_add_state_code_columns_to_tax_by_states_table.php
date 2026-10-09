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
        if (! Schema::hasColumn('tax_by_states', 'state_code')) {
            Schema::table('tax_by_states', function (Blueprint $table): void {
                $table->string('state_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_by_states', function (Blueprint $table): void {
            $table->dropColumn('state_code');
        });
    }
};
