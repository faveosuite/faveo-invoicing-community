<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * invoices.date is meant to be the invoice's business date, set once at
     * creation. On this environment the live schema somehow carries
     * `ON UPDATE CURRENT_TIMESTAMP` on it — not declared by any migration —
     * so MySQL silently rewrites it to "now" on ANY update to the row that
     * doesn't explicitly re-send the same date (a payment, a status refresh,
     * an unrelated admin edit). That corrupts monthly/yearly/total sales
     * figures, which group by this column. This strips only the dangerous
     * ON UPDATE clause; default-on-insert and NOT NULL are left as-is.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE invoices MODIFY `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE invoices MODIFY `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }
};
