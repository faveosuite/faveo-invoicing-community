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
        Schema::dropIfExists('countries');

        Schema::create('countries', function (Blueprint $table) {
            $table->mediumIncrements('country_id');
            $table->char('country_code_char2', 2)->nullable();
            $table->char('country_code_char3', 3)->nullable();
            $table->string('country_name', 100);
            $table->char('numcode', 3)->nullable();
            $table->string('phonecode')->nullable();
            $table->string('capital')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('emoji', 191)->nullable();
            $table->string('emojiU', 191)->nullable();
            $table->unsignedInteger('currency_id');
            $table->boolean('status')->default(true);
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
