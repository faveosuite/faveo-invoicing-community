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
        if (! Schema::hasTable('mailchimp_field_agora_relations')) {
            Schema::create('mailchimp_field_agora_relations', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('company')->nullable();
                $table->string('mobile')->nullable();
                $table->string('address')->nullable();
                $table->string('country')->nullable();
                $table->string('town')->nullable();
                $table->string('state')->nullable();
                $table->string('zip')->nullable();
                $table->string('active', 225)->nullable();
                $table->string('role')->nullable();
                $table->string('source')->nullable();
                $table->string('is_paid_yes')->nullable();
                $table->string('is_paid_no')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('mailchimp_field_agora_relations');
    }
};
