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
        Schema::create('zoho_oauth_clients', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('integration_id')
                ->constrained('zoho_integrations')
                ->cascadeOnDelete();

            $table->string('client_id');
            $table->string('client_secret');
            $table->string('redirect_uri');

            $table->enum('region', ['au', 'ca', 'cn', 'eu', 'in', 'jp', 'sa', 'us'])->default('in');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_oauth_clients');
    }
};
