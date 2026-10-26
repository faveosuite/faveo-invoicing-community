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
        if (! Schema::hasTable('mailchimp_lists')) {
            Schema::create('mailchimp_lists', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name');
                $table->string('list_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('mailchimp_lists');
    }
};
