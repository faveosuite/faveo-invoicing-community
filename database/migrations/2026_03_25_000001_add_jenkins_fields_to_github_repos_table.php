<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('github_repos', function (Blueprint $table) {
            $table->string('jenkins_url')->nullable()->after('dispatch_branch');
            $table->string('jenkins_job')->nullable()->after('jenkins_url');
        });
    }

    public function down()
    {
        Schema::table('github_repos', function (Blueprint $table) {
            $table->dropColumn(['jenkins_url', 'jenkins_job']);
        });
    }
};
