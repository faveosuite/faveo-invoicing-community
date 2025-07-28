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
        Schema::table('widgets', function (Blueprint $table) {
            if (! Schema::hasColumn('widgets', 'allow_tweets')) {
                $table->boolean('allow_tweets')->nullable();
            }
            if (! Schema::hasColumn('widgets', 'allow_mailchimp')) {
                $table->boolean('allow_mailchimp')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('widgets', function (Blueprint $table) {
            $table->dropColumn([
                'allow_tweets', 'allow_mailchimp',
            ]);
        });
    }
};
