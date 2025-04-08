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
        Schema::create('msg_delivery_reports', function (Blueprint $table) {
            $table->id();
            $table->string('mobile_number');
            $table->string('request_id')->unique()->nullable();
            $table->string('status')->nullable();
            $table->dateTime('date')->nullable();
            $table->string('sender_id')->nullable();
            $table->string('failure_reason')->nullable();
            $table->string('user_id')->nullable();
            $table->string('country_iso')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('msg_delivery_reports');
    }
};
