<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('github_repos', function (Blueprint $table) {
            $table->string('dispatch_branch')->default('development')->after('workflow_file');
        });
    }

    public function down()
    {
        Schema::table('github_repos', function (Blueprint $table) {
            $table->dropColumn('dispatch_branch');
        });
    }
};
