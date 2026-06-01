<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill invoice_tax_lines for historical invoices from the legacy per-item
 * tax fields (invoice_items.tax_name / tax_percentage), so ALL invoices expose
 * tax the same way and display can rely on a single source.
 *
 * Legacy data is messy: percentages appear as "18", "18%", "18,", "[0,0,0]";
 * labels carry trailing commas ("GST,"). The parser normalises both.
 *
 * Backfilled rows have tax_rate_id = NULL (the originating rate no longer
 * exists / is unknown) — which also lets down() identify and remove them
 * without touching cart-created lines (those carry a real tax_rate_id).
 * Idempotent: invoices that already have any tax line are skipped.
 */
return new class extends Migration
{
    public function up()
    {
        $invoicesWithLines = DB::table('invoice_tax_lines')->distinct()->pluck('invoice_id')->flip();

        DB::table('invoice_items')
            ->whereNotNull('tax_name')
            ->where('tax_name', '!=', '')
            ->where('tax_name', 'not like', 'null%')
            ->orderBy('id')
            ->chunkById(1000, function ($items) use ($invoicesWithLines) {
                $rows = [];
                foreach ($items as $item) {
                    if (isset($invoicesWithLines[$item->invoice_id])) {
                        continue; // already has tax lines (new cart invoices)
                    }
                    $label = $this->cleanLabel($item->tax_name);
                    $percent = $this->parsePercent($item->tax_percentage);
                    if ($label === '' || strtolower($label) === 'null' || $percent <= 0) {
                        continue;
                    }
                    $rows[] = [
                        'invoice_id' => $item->invoice_id,
                        'invoice_item_id' => $item->id,
                        'tax_rate_id' => null,
                        'label' => $label,
                        'rate' => $percent,
                        'compound' => 0,
                        'amount' => round((float) $item->subtotal * $percent / 100, 4),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                if ($rows) {
                    DB::table('invoice_tax_lines')->insert($rows);
                }
            });
    }

    public function down()
    {
        // Only the backfilled rows (no rate reference) — leave cart-created
        // lines (which carry a tax_rate_id) intact.
        DB::table('invoice_tax_lines')->whereNull('tax_rate_id')->delete();
    }

    private function cleanLabel($raw): string
    {
        return trim(trim((string) $raw), ", \t\n");
    }

    private function parsePercent($raw): float
    {
        $raw = str_replace(['[', ']', '%'], '', (string) $raw);
        $sum = 0.0;
        foreach (explode(',', $raw) as $part) {
            $part = trim($part);
            if ($part !== '' && is_numeric($part)) {
                $sum += (float) $part;
            }
        }

        return $sum;
    }
};
