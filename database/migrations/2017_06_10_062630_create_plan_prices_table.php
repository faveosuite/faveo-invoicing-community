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
        if (! Schema::hasTable('plan_prices')) {
            Schema::create('plan_prices', function (Blueprint $table): void {
                $table->increments('id');
                $table->integer('plan_id')->unsigned();
                $table->string('currency');
                $table->string('add_price');
                $table->string('renew_price', 225);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('plan_prices');
    }
};
