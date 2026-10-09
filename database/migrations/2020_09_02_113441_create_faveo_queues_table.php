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
        if (! Schema::hasTable('faveo_queues')) {
            Schema::create('faveo_queues', function (Blueprint $table): void {
                $table->increments('id');
                $table->integer('service_id');
                $table->string('key');
                $table->string('value');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faveo_queues');
    }
};
