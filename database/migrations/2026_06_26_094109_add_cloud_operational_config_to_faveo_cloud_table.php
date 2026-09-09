<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faveo_cloud', function (Blueprint $table): void {
            $table->string('cloud_job_url')->nullable()->after('cloud_cname');
            $table->string('cloud_job_url_normal')->nullable()->after('cloud_job_url');
            $table->string('cloud_user')->nullable()->after('cloud_job_url_normal');
            $table->string('cloud_delete_job_url_normal')->nullable()->after('cloud_user');
            $table->string('cloud_delete_job_url_custom')->nullable()->after('cloud_delete_job_url_normal');
            $table->text('cloud_auth')->nullable()->after('cloud_delete_job_url_custom');
            $table->text('cloud_oauth_token')->nullable()->after('cloud_auth');
            $table->text('google_chat_webhook')->nullable()->after('cloud_oauth_token');
        });
    }

    public function down(): void
    {
        Schema::table('faveo_cloud', function (Blueprint $table): void {
            $table->dropColumn([
                'cloud_job_url', 'cloud_job_url_normal', 'cloud_user',
                'cloud_delete_job_url_normal', 'cloud_delete_job_url_custom',
                'cloud_auth', 'cloud_oauth_token', 'google_chat_webhook',
            ]);
        });
    }
};
