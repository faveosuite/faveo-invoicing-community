<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_signing_keys', function (Blueprint $table): void {
            $table->id();
            $table->text('public_key');
            $table->text('secret_key');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_signing_keys');
    }
};
