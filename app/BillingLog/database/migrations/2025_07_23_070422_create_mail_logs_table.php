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
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('log_category_id')->index();
            $table->string('referee_id');
            $table->string('referee_type');

            $table->string('sender_mail')->index();
            $table->string('receiver_mail')->index();

            $table->string('collaborator')->nullable();

            $table->binary('subject');
            $table->longText('body')->charset('binary');
            $table->longText('job_payload')->charset('binary');

            $table->string('source');
            $table->string('status', 30)->nullable()->index();

            $table->unsignedBigInteger('exception_log_id')->nullable();

            $table->timestamp('created_at')->nullable()->index();
            $table->timestamp('updated_at')->nullable()->index();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_logs');
    }
};
