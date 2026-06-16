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
        Schema::create('zoho_fields', function (Blueprint $table): void {
            $table->id();

            $table->string('platform'); // crm, campaigns, desk
            $table->string('module');   // Contacts, Leads, Tickets

            $table->string('zoho_field_uid');
            $table->string('zoho_key');

            $table->string('display_name');
            $table->string('field_type')->nullable();

            $table->boolean('is_mandatory')->default(false);

            $table->json('raw_metadata');

            $table->timestamps();

            $table->unique(
                ['platform', 'module', 'zoho_field_uid'],
                'zoho_fields_unique'
            );

            $table->index(['platform', 'module']);
            $table->index(['platform', 'zoho_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_fields');
    }
};
