<?php

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix regular_price values stored as JSON arrays (e.g. '["0"]'), empty arrays,
     * values with text suffixes, or leading whitespace.
     * Root cause: pluck() instead of value() in FreeTrailController.
     */
    public function up(): void
    {
        DB::table('invoice_items')
            ->where(function (Builder $q): void {
                $q->where('regular_price', '[]')
                    ->orWhereRaw("JSON_VALID(regular_price) AND JSON_TYPE(regular_price) = 'ARRAY'")
                    ->orWhereRaw("regular_price REGEXP '^[0-9]+/'")
                    ->orWhereRaw("regular_price REGEXP '(^\\\\s+|\\\\s+$)'");
            })
            ->update([
                'regular_price' => DB::raw("
            CASE
                WHEN regular_price = '[]' THEN '0'
                WHEN JSON_VALID(regular_price) AND JSON_TYPE(regular_price) = 'ARRAY'
                    THEN JSON_UNQUOTE(JSON_EXTRACT(regular_price, '$[0]'))
                WHEN regular_price REGEXP '^[0-9]+/'
                    THEN SUBSTRING_INDEX(regular_price, '/', 1)
                ELSE TRIM(regular_price)
            END
        "),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible — original corrupted data should not be restored.
    }
};
