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
        Schema::table('widgets', function (Blueprint $table): void {
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
     */
    public function down(): void
    {
        Schema::table('widgets', function (Blueprint $table): void {
            $table->dropColumn([
                'allow_tweets', 'allow_mailchimp',
            ]);
        });
    }
};
