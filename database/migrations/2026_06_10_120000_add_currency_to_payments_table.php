<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a currency column to payments and backfill existing rows.
     *
     * Backfill order (each pass only touches rows still without a currency):
     *   1. Invoice-linked payments  -> the linked invoice's currency (recorded fact).
     *   2. Credit/standalone rows   -> the client's account currency, but only when
     *                                  it is a real currency code (filters junk values).
     *   3. Anything still missing   -> derived from the client's country, else USD.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('payments', 'currency')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('currency', 10)->nullable()->after('amount');
            });
        }

        // 1) Invoice-linked payments → invoice currency.
        DB::statement("
            UPDATE payments p
            INNER JOIN invoices i ON i.id = p.invoice_id
            SET p.currency = i.currency
            WHERE p.invoice_id > 0
              AND (p.currency IS NULL OR p.currency = '')
              AND i.currency IS NOT NULL AND i.currency != ''
        ");

        // 2) Remaining rows → client's account currency, but only valid currency codes
        //    (joining `currencies` filters out junk like '1', '2', '').
        DB::statement("
            UPDATE payments p
            INNER JOIN users u ON u.id = p.user_id
            INNER JOIN currencies c ON c.code = u.currency
            SET p.currency = u.currency
            WHERE (p.currency IS NULL OR p.currency = '')
        ");

        // 3) Anything still missing → derive from the client's country, else USD.
        DB::table('payments')
            ->select('payments.id', 'users.country')
            ->leftJoin('users', 'users.id', '=', 'payments.user_id')
            ->where(function ($q) {
                $q->whereNull('payments.currency')->orWhere('payments.currency', '');
            })
            ->orderBy('payments.id')
            ->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $currency = $row->country ? getCurrencyForClient($row->country) : null;
                    if (empty($currency)) {
                        $currency = 'USD';
                    }
                    DB::table('payments')->where('id', $row->id)->update(['currency' => $currency]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('payments', 'currency')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};
