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
        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table): void {
                $table->bigInteger('id', autoIncrement: true)->unsigned();
                $table->string('queue');
                $table->text('payload');
                $table->boolean('attempts');
                $table->boolean('reserved');
                $table->integer('reserved_at')->unsigned()->nullable();
                $table->integer('available_at')->unsigned();
                $table->integer('created_at')->unsigned();
                $table->index(['queue', 'reserved', 'reserved_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('jobs');
    }
};
