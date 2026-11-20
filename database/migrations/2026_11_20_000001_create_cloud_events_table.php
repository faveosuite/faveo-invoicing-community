<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Outbox of events billing sends to Faveo Cloud central (license.updated, license.suspended, …).
 * Each row is sent by `cloud:events` and retried with growing gaps until the cloud accepts it,
 * or for 3 days (then failed_at is set and an admin can resend it). The id is the event id the
 * cloud uses to skip duplicates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cloud_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type', 40);
            $table->string('tenant')->nullable(); // the cloud domain it is about
            $table->json('payload');              // what send() needs to build the event data
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['sent_at', 'failed_at', 'next_attempt_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cloud_events');
    }
};
