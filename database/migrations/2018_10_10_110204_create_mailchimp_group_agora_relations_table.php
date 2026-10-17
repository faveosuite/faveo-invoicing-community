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
        if (! Schema::hasTable('mailchimp_group_agora_relations')) {
            Schema::create('mailchimp_group_agora_relations', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('mailchimp_group_cat_id', 255)->nullable();
                $table->string('agora_product_id', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailchimp_group_agora_relations');
    }
};
