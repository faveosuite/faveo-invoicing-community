<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * How a plan sets the end date of a date feature (updates, support, …). Stored on the plan's
 * feature row as JSON, e.g. {"ends":"custom","every":90,"unit":"day"}. Each option also
 * decides what happens at renewal:
 *
 *  - subscription: ends with the subscription, so it follows every renewal and plan change
 *  - custom:       N days / months / years from purchase; a renewal adds N again, from the
 *                  current end, or from today when it has already ended
 *  - month_end:    the last day of this month; a renewal moves it to the next month's end
 *  - year_end:     31 December of this year; a renewal moves it to the next year's end
 *  - fixed:        one date
 *  - never:        no end
 *
 * A plan change (event "change") restarts every option except "subscription", which follows.
 */
final class DateRules
{
    public const array ENDS = ['subscription', 'custom', 'month_end', 'year_end', 'fixed', 'never'];

    public const array UNITS = ['day', 'month', 'year'];

    /** A date feature on a plan with no option set ends with the subscription. */
    public const array DEFAULT = ['ends' => 'subscription'];

    /**
     * Validates an option from the admin screen (array or JSON) and returns it as stored JSON,
     * null for the default, or false when it doesn't fit.
     */
    public static function normalize(mixed $value): string|false|null
    {
        if ($value === null || $value === '') {
            return null;
        }
        $rule = is_string($value) ? json_decode($value, true) : $value;
        if (! is_array($rule) || ! in_array($rule['ends'] ?? null, self::ENDS, strict: true)) {
            return false;
        }

        $clean = match ($rule['ends']) {
            'custom' => filter_var($rule['every'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 3650]]) !== false
                && in_array($rule['unit'] ?? null, self::UNITS, strict: true)
                    ? ['ends' => 'custom', 'every' => (int) $rule['every'], 'unit' => $rule['unit']] : false,
            'fixed' => is_string($rule['date'] ?? null) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $rule['date']) === 1 && strtotime($rule['date']) !== false
                ? ['ends' => 'fixed', 'date' => $rule['date']] : false,
            default => ['ends' => $rule['ends']],
        };

        return $clean === false ? false : (string) json_encode($clean);
    }

    /**
     * @return array{ends: string, every?: int, unit?: string, date?: string}
     */
    public static function decode(?string $stored): array
    {
        $rule = $stored !== null ? json_decode($stored, true) : null;
        if (! is_array($rule) || ! is_string($rule['ends'] ?? null) || ! in_array($rule['ends'], self::ENDS, strict: true)) {
            return self::DEFAULT;
        }

        $out = ['ends' => $rule['ends']];
        if (is_int($rule['every'] ?? null)) {
            $out['every'] = $rule['every'];
        }
        if (is_string($rule['unit'] ?? null)) {
            $out['unit'] = $rule['unit'];
        }
        if (is_string($rule['date'] ?? null)) {
            $out['date'] = $rule['date'];
        }

        return $out;
    }

    /**
     * The new end date (Y-m-d, null = never) for one date feature; "subscription" returns the
     * subscription end exactly as given.
     *
     * @param  array{ends: string, every?: int, unit?: string, date?: string}  $rule
     * @param  'purchase'|'renewal'|'change'  $event
     * @param  string|null  $current  its end date today (Y-m-d), for renewals
     * @param  string|null  $subscriptionEnd  the subscription's (new) end (null = never)
     */
    public static function ends(array $rule, string $event, ?string $current, ?string $subscriptionEnd): ?string
    {
        $today = Carbon::today();
        // A renewal continues from a date still running; anything else starts today.
        $from = $event === 'renewal' && $current !== null && Carbon::parse($current)->gte($today) ? Carbon::parse($current) : null;

        if ($rule['ends'] === 'subscription') {
            return $subscriptionEnd; // as given, time included: old columns keep the exact subscription end
        }

        $end = match ($rule['ends']) {
            'custom' => ($from ?? $today)->copy()->{match ($rule['unit'] ?? 'day') {
                'month' => 'addMonthsNoOverflow',
                'year' => 'addYearsNoOverflow',
                default => 'addDays',
            }}((int) ($rule['every'] ?? 0)),
            'month_end' => $from ? $from->copy()->addMonthNoOverflow()->endOfMonth() : $today->copy()->endOfMonth(),
            'year_end' => $from ? $from->copy()->addYearNoOverflow()->endOfYear() : $today->copy()->endOfYear(),
            'fixed' => Carbon::parse((string) ($rule['date'] ?? '')),
            default => null,
        };

        return $end?->toDateString();
    }
}
