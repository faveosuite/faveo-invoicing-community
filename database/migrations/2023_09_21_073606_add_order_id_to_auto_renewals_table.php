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
        if (! Schema::hasColumn('amount', 'payment_type')) {
            Schema::table('auto_renewals', function (Blueprint $table): void {
                $table->integer('order_id')->unsigned()->index('auto_renewals_order_id_foreign');
                $table->string('payment_method')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_renewals', function (Blueprint $table): void {
            $table->dropColumn(['order_id', 'payment_method']);
        });
    }
};
