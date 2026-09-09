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
        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table): void {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->index('invoices_user_id_foreign');
                $table->string('number');
                $table->timestamp('date')->nullable();
                $table->string('discount')->nullable();
                $table->string('discount_mode')->nullable();
                $table->string('coupon_code')->nullable();
                $table->string('grand_total', 225);
                $table->string('currency', 225)->default('USD');
                $table->string('status', 225);
                $table->string('description', 225)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('invoices');
    }
};
