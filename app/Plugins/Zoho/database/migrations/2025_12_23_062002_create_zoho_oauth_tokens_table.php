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
        Schema::create('zoho_oauth_tokens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('integration_id')
                ->constrained('zoho_integrations')
                ->cascadeOnDelete()
                ->unique();

            $table->longText('access_token');
            $table->longText('refresh_token');

            $table->timestamp('expires_at');
            $table->text('scope')->nullable();
            $table->string('api_domain')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_oauth_tokens');
    }
};
