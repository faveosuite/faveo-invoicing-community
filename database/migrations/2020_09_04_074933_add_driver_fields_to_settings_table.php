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
        Schema::table('settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('settings', 'key')) {
                $table->string('key')->nullable();
            }

            if (! Schema::hasColumn('settings', 'secret')) {
                $table->string('secret')->nullable();
            }

            if (! Schema::hasColumn('settings', 'region')) {
                $table->string('region')->nullable();
            }

            if (! Schema::hasColumn('settings', 'domain')) {
                $table->string('domain')->nullable();
            }

            if (! Schema::hasColumn('settings', 'sending_status')) {
                $table->boolean('sending_status')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn('key');
            $table->dropColumn('secret');
            $table->dropColumn('region');
            $table->dropColumn('domain');
            $table->dropColumn('sending_status');
        });
    }
};
