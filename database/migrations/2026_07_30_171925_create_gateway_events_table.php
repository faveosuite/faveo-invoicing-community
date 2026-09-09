<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records every gateway webhook event this app has already processed, so
     * a redelivery of the same event (Stripe/Razorpay both explicitly
     * guarantee only at-least-once delivery, i.e. retries/redeliveries are
     * normal, expected behaviour) can be recognized and skipped instead of
     * being processed again — without this, a redelivered "renewal charged"
     * event creates a duplicate invoice + payment and extends the
     * subscription's dates a second time for one real charge.
     *
     * Generic by design (gateway + the gateway's own event id), not scoped
     * to renewals specifically, so any other webhook-driven action can reuse
     * the same guard.
     */
    public function up(): void
    {
        if (! Schema::hasTable('gateway_events')) {
            Schema::create('gateway_events', function (Blueprint $table): void {
                $table->id();
                $table->string('gateway');
                $table->string('event_id');
                $table->timestamps();
                $table->unique(['gateway', 'event_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_events');
    }
};
