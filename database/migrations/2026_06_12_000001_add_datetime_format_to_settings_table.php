<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->string('date_format', 20)->default('d/m/Y')->after('timezone_id');
            $table->string('time_format', 20)->default('H:i')->after('date_format');
        });

        // Default timezone to UTC (id=114) for rows that have none set
        DB::table('settings')->where('timezone_id', 0)->orWhereNull('timezone_id')->update(['timezone_id' => 114]);
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn(['date_format', 'time_format']);
        });
    }
};
