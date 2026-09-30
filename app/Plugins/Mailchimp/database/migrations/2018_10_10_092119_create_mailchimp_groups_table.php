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
        if (! Schema::hasTable('mailchimp_groups')) {
            Schema::create('mailchimp_groups', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('category_id', 255)->nullable();
                $table->string('list_id', 255)->nullable();
                $table->string('category_option_id', 255)->nullable();
                $table->string('category_name', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailchimp_groups');
    }
};
