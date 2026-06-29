<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('github_repos')) {
            Schema::create('github_repos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('display_name');
                $table->string('owner');
                $table->string('repo');
                $table->string('workflow_file')->default('release.yml');
                $table->string('dispatch_branch')->default('development');
                $table->string('jenkins_url')->nullable();
                $table->string('jenkins_job')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('github_repos');
    }
};
