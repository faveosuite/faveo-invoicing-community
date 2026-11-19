<?php

declare(strict_types=1);

namespace App\Services;

/**
 * The old update and support date columns on licenses and subscriptions. Grants are the source;
 * these columns are copies kept for old product versions, reminder emails and reports.
 * ponytail: delete this class with the columns once every reader uses grants.
 */
final class LegacyDates
{
    /** Date feature code => [license column, subscription column]. */
    public const array COLUMNS = [
        'updates' => ['license_updates_date', 'update_ends_at'],
        'support' => ['license_support_date', 'support_ends_at'],
    ];

    /**
     * @param  array<string, string|null>  $ends  code => Y-m-d
     * @return array<string, string|null> license column => Y-m-d
     */
    public static function forLicense(array $ends): array
    {
        return self::columns($ends, 0);
    }

    /**
     * @param  array<string, string|null>  $ends  code => Y-m-d
     * @return array<string, string|null> subscription column => Y-m-d
     */
    public static function forSubscription(array $ends): array
    {
        return self::columns($ends, 1);
    }

    /**
     * @param  array<string, string|null>  $ends
     * @return array<string, string|null>
     */
    private static function columns(array $ends, int $side): array
    {
        $out = [];
        foreach (self::COLUMNS as $code => $columns) {
            if (array_key_exists($code, $ends)) {
                $out[$columns[$side]] = $ends[$code];
            }
        }

        return $out;
    }
}
