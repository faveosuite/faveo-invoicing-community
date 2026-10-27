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
        Schema::create('zoho_integrations', function (Blueprint $table): void {
            $table->id();
            $table->enum('platform', ['crm', 'campaigns'])->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(value: false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_integrations');
    }
};
