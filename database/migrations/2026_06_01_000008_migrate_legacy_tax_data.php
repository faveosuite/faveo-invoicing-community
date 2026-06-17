<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One-time conversion of the legacy India-GST tax data into the new generic
 * tax_rates model.
 *
 * Decisions (confirmed with product owner):
 *  - Drop the CGST/SGST/IGST split. India GST becomes a single named rate.
 *  - Every product that was taxable (had a tax_product_relation) is moved to
 *    the Standard tax class; all migrated rates live in the Standard class.
 *  - Legacy tables (taxes, tax_by_states, tax_classes legacy rows) are left
 *    intact so the conversion can be re-derived if needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Slugify any pre-existing legacy classes FIRST so none keep the
        // default empty slug — otherwise ensureClass('Standard', '') would
        // match a legacy row instead of creating the canonical Standard class.
        foreach (DB::table('tax_classes')->get() as $class) {
            if ($class->slug === '' || $class->slug === null) {
                DB::table('tax_classes')->where('id', $class->id)
                    ->update(['slug' => Str::slug($class->name) ?: 'class-'.$class->id]);
            }
        }

        // Only the Standard class is seeded by default; admins add their own
        // custom classes from the Tax settings screen.
        $standardId = $this->ensureClass('Standard', '');

        // All previously-taxable products become Standard-class taxable.
        if (DB::getSchemaBuilder()->hasTable('tax_product_relations')) {
            DB::table('tax_product_relations')->update(['tax_class_id' => $standardId]);
        }

        $this->migrateIndiaGst();
        $this->migrateOtherTaxes();
    }

    public function down(): void
    {
        // Forward-only data migration. Clear the generated rates; the seeded
        // default classes are removed so a re-run starts clean. Legacy tables
        // remain the source of truth.
        DB::table('tax_rate_locations')->delete();
        DB::table('tax_rates')->delete();
        DB::table('tax_classes')->where('slug', '')->where('name', 'Standard')->delete();
    }

    /** Create the class if its slug is absent; return the class id. */
    private function ensureClass(string $name, string $slug): int
    {
        $existing = DB::table('tax_classes')->where('slug', $slug)->first();
        if ($existing) {
            return (int) $existing->id;
        }

        return (int) DB::table('tax_classes')->insertGetId([
            'name' => $name,
            'slug' => $slug,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Collapse tax_by_states into Standard-class GST rate(s). If every state
     * shares the same inter-state rate (the common case: 18%), a single
     * country-wide rate is created; otherwise one rate per state.
     */
    private function migrateIndiaGst(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('tax_by_states')) {
            return;
        }

        $isNull = fn ($v): bool => $v === null || strtoupper((string) $v) === 'NULL' || $v === '';

        $rows = DB::table('tax_by_states')->where('country', 'IN')->get();
        $distinct = $rows->reject(fn ($r): bool => $isNull($r->i_gst))
            ->pluck('i_gst')->map(fn ($v): float => (float) $v)->unique()->values();

        if ($distinct->count() === 1) {
            $this->insertRate('GST', 'IN', '', $distinct->first());

            return;
        }

        foreach ($rows as $row) {
            if ($isNull($row->i_gst)) {
                continue;
            }

            $this->insertRate('GST', 'IN', (string) $row->state_code, (float) $row->i_gst);
        }
    }

    /**
     * Migrate the legacy "Others" taxes (non-GST, used for other countries)
     * into Standard-class rates.
     */
    private function migrateOtherTaxes(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('taxes')) {
            return;
        }

        $gstClassIds = DB::table('tax_classes')
            ->whereIn('name', ['Intra State GST', 'Inter State GST', 'Union Territory GST'])
            ->pluck('id')->all();

        $rows = DB::table('taxes')
            ->when($gstClassIds, fn ($q) => $q->whereNotIn('tax_classes_id', $gstClassIds))
            ->get();

        foreach ($rows as $row) {
            $rate = (float) str_replace('%', '', (string) $row->rate);
            if ($rate <= 0) {
                continue;
            }

            $this->insertRate(
                $row->name ?: 'Tax',
                (string) ($row->country ?? ''),
                (string) ($row->state ?? ''),
                $rate,
                (int) ($row->active ?? 1),
                (int) ($row->compound ?? 0)
            );
        }
    }

    private function insertRate(string $name, string $country, string $state, float $rate, int $active = 1, int $compound = 0): void
    {
        DB::table('tax_rates')->insert([
            'name' => $name,
            'country' => strtoupper($country),
            'state' => $state,
            'rate' => $rate,
            'priority' => 1,
            'compound' => (bool) $compound,
            'tax_class' => '',
            'display_order' => 0,
            'active' => (bool) $active,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
