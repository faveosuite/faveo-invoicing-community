<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An Agents extra can be negative: an order kept on fewer agents than its plan now includes
     * (plan price minus those agents).
     */
    public function up(): void
    {
        Schema::table('order_entitlements', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->integer('renew_quantity')->nullable()->change();
        });
    }

    /** Fails once any quantity is negative (a "fewer agents" extra): unsigned can't hold it. */
    public function down(): void
    {
        Schema::table('order_entitlements', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->change();
            $table->unsignedInteger('renew_quantity')->nullable()->change();
        });
    }
};
