<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('is_subscribed', 'subscribe_id')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->boolean('is_subscribed')->nullable();
                $table->string('subscribe_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['is_subscribed', 'subscribe_id']);
        });
    }
};
