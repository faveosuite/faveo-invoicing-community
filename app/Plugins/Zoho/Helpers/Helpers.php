<?php

use App\Plugins\Zoho\Controllers\Api\ZohoRegion;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use Illuminate\Support\Collection;

/**
 * Map local data to Zoho field values using saved mappings.
 *
 * @param  Collection<int|string, mixed>  $mappings
 * @param  array<mixed>  $source
 * @param  Collection<int|string, mixed>  $zohoFields
 *
 * @phpstan-param Collection<array-key, mixed> $zohoFields
 * @phpstan-param Collection<array-key, mixed> $mappings
 *
 * @return array<mixed>
 */
function zohoMappedFields(
    Collection $zohoFields,
    Collection $mappings,
    array|object $source
): array {
    $result = [];

    $zohoById = $zohoFields->keyBy('id');

    foreach ($mappings as $mapping) {
        $zohoField = $zohoById->get($mapping->zoho_field_id);

        if (! $zohoField) {
            continue;
        }

        $zohoKey = $zohoField->zoho_key;

        $selected = resolveSelected($mapping);

        if ($selected !== null && $selected['type'] === 'local') {
            $updateKey = $mapping->faveoLocalField->field_key;
            $value = data_get($source, $updateKey, $mapping->default_value ?? null);
        } elseif ($selected !== null && $selected['type'] === 'zoho') {
            $value = json_decode((string) $selected['value'], associative: true)['value'] ?? null;
        } else {
            $value = null;
        }

        if ($value === null) {
            continue;
        }

        if ($value === '') {
            continue;
        }

        $result[$zohoKey] = $value;
    }

    return $result;
}

/**
 * Zoho's own defined choices for a picklist field, if any (excludes the
 * `-None-` placeholder). `pick_list_values` is CRM's metadata shape only —
 * Zoho Campaigns picklist fields (e.g. Lead Source) don't carry it, so this
 * comes back empty for those. Shared by resolveOptions() (what to offer in
 * the mapping UI) and ZohoConnectHelper's field-type compatibility check
 * (whether a local-field mapping is safe to allow).
 *
 * @return Collection<int, array{type: string, value: mixed, label: mixed}>
 */
function zohoPicklistOptions(mixed $zohoField): Collection
{
    return collect((array) ($zohoField->raw_metadata['pick_list_values'] ?? []))
        ->reject(fn ($opt): bool => ($opt['actual_value'] ?? null) === '-None-')
        ->map(fn ($opt): array => [
            'type' => 'zoho',
            'value' => $opt['actual_value'],
            'label' => $opt['display_value'],
        ])
        ->values();
}

/**
 * Resolve selectable options for a Zoho field.
 *
 * @param  Collection<int|string, mixed>  $localFields
 *
 * @phpstan-param Collection<array-key, mixed> $localFields
 *
 * @return array<mixed>
 */
function resolveOptions(mixed $zohoField, Collection $localFields): array
{
    if ($zohoField->field_type === 'picklist') {
        $zohoOptions = zohoPicklistOptions($zohoField);

        // ponytail: falling back to local fields keeps a no-options picklist
        // (e.g. Campaigns) mappable at all; wire up Campaigns' real choice
        // format here if/when needed.
        if ($zohoOptions->isNotEmpty()) {
            return $zohoOptions->all();
        }
    }

    return $localFields->map(fn ($local): array => [
        'type' => 'local',
        'value' => $local->id,
        'label' => $local->display_name,
    ])->values()->all();
}

/**
 * Resolve selected mapping for a Zoho field.
 *
 * @return array<mixed>
 */
function resolveSelected(?ZohoFieldMappings $mapping): ?array
{
    if (! $mapping instanceof ZohoFieldMappings) {
        return null;
    }

    if (! empty($mapping->faveo_local_field_id)) {
        return [
            'type' => 'local',
            'value' => $mapping->faveo_local_field_id,
        ];
    }

    return [
        'type' => 'zoho',
        'value' => $mapping->selected_option,
    ];
}

function getZohoRegion(string $region): ZohoRegion
{
    $default = config('zoho.default_region');

    return ZohoRegion::tryFrom($region)
        ?? ZohoRegion::tryFrom($default)
        ?? ZohoRegion::India;
}
