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
        if (! Schema::hasTable('verification_attempts')) {
            Schema::create('verification_attempts', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->integer('user_id')->unsigned()->index('verification_attempts_user_id_foreign');
                $table->string('type');
                $table->unsignedInteger('attempt_count');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_attempts');
    }
};
