<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Clients now read the update period from the licence (licenses.license_updates_date, sent by
     * /api/licenseVerify) instead of asking /v1/checkUpdatesExpiry, which read the subscription.
     * A licence with no date means "updates never expire" to the client, so copy the subscription's
     * date into licences that have none while their subscription does.
     */
    public function up(): void
    {
        DB::table('licenses')
            ->join('orders', 'orders.number', '=', 'licenses.license_order_number')
            ->join('subscriptions', 'subscriptions.order_id', '=', 'orders.id')
            ->whereNull('licenses.license_updates_date')
            ->whereNotNull('subscriptions.update_ends_at')
            ->update(['licenses.license_updates_date' => DB::raw('DATE(subscriptions.update_ends_at)')]);
    }

    public function down(): void
    {
        // down we dont need here
    }
};
