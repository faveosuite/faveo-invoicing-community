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
        Schema::create('whatsapp_integration_user', function (Blueprint $table) {
            $table->id();
            $table->string('waba_id');
            $table->string('phone_number_id');
            $table->string('business_id');
            $table->string('phone_number');
            $table->longText('access_token');
            $table->foreignId('user_id')->constrained();
            $table->string('user_callback_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_integration_user');
    }
};
