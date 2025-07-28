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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'google2fa_secret')) {
                $table->text('google2fa_secret')->nullable();
            }
            if (! Schema::hasColumn('users', 'google2fa_activation_date')) {
                $table->timestamp('google2fa_activation_date')->nullable();
            }
            if (! Schema::hasColumn('users', 'is_2fa_enabled')) {
                $table->boolean('is_2fa_enabled')->default(0);
            }
            if (! Schema::hasColumn('users', 'backup_code')) {
                $table->string('backup_code')->nullable();
            }
            if (! Schema::hasColumn('users', 'code_usage_count')) {
                $table->integer('code_usage_count')->default(0);
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google2fa_secret');
            $table->dropColumn('google2fa_activation_date');
            $table->dropColumn('is_2fa_enabled');
            $table->dropColumn('backup_code');
            $table->dropColumn('code_usage_count');
        });
    }
};
