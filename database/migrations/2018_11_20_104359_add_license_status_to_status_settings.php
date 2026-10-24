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
        Schema::table('status_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('status_settings', 'license_status')) {
                $table->integer('license_status');
            }

            if (! Schema::hasColumn('status_settings', 'github_status')) {
                $table->integer('github_status');
            }

            if (! Schema::hasColumn('status_settings', 'mailchimp_status')) {
                $table->integer('mailchimp_status');
            }

            if (! Schema::hasColumn('status_settings', 'twitter_status')) {
                $table->integer('twitter_status');
            }

            if (! Schema::hasColumn('status_settings', 'msg91_status')) {
                $table->integer('msg91_status');
            }

            if (! Schema::hasColumn('status_settings', 'emailverification_status')) {
                $table->integer('emailverification_status');
            }

            if (! Schema::hasColumn('status_settings', 'recaptcha_status')) {
                $table->integer('recaptcha_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'github_status',
                'license_status',
                'mailchimp_status',
                'twitter_status',
                'msg91_status',
                'emailverification_status',
                'recaptcha_status',
            ]);
        });
    }
};
