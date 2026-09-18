<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // AutoRenewalActivationService::activate() checked-then-inserted with
        // no DB-level guard, so two near-simultaneous triggers for the same
        // order+gateway (a redirect-confirm racing a webhook) could both pass
        // the check before either insert landed — duplicate rows exist in
        // production right now as a result. Keep the earliest row per group
        // (the one that recorded the real activation) before the constraint
        // below would otherwise reject them.
        $duplicateGroups = DB::table('auto_renewals')
            ->select('order_id', 'payment_method')
            ->whereNotNull('payment_method')
            ->groupBy('order_id', 'payment_method')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $idToKeep = DB::table('auto_renewals')
                ->where('order_id', $group->order_id)
                ->where('payment_method', $group->payment_method)
                ->orderBy('id')
                ->value('id');

            DB::table('auto_renewals')
                ->where('order_id', $group->order_id)
                ->where('payment_method', $group->payment_method)
                ->where('id', '!=', $idToKeep)
                ->delete();
        }

        Schema::table('auto_renewals', function (Blueprint $table): void {
            $table->unique(['order_id', 'payment_method'], 'auto_renewals_order_gateway_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_renewals', function (Blueprint $table): void {
            $table->dropUnique('auto_renewals_order_gateway_unique');
        });
    }
};
