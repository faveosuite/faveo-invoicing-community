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
        if (! Schema::hasTable('payment_logs')) {
            Schema::create('payment_logs', function (Blueprint $table): void {
                $table->id();
                $table->dateTime('date');
                $table->string('from')->nullable();
                $table->string('to')->nullable();
                $table->string('subject');
                $table->text('body');
                $table->string('status', 255)->nullable();
                $table->text('exception')->nullable();
                $table->string('order')->nullable();
                $table->string('payment_method')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
