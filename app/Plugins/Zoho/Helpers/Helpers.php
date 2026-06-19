<?php

use App\Plugins\Zoho\Controllers\Api\ZohoRegion;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use Illuminate\Support\Collection;

/**
 * Map local data to Zoho field values using saved mappings.
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

        if ($selected['type'] === 'local') {
            $updateKey = $mapping->faveoLocalField->field_key;
            $value = data_get($source, $updateKey, $mapping->default_value);
        }

        $value = null;
        if ($selected['type'] === 'zoho') {
            $value = json_decode((string) $selected['value'], associative: true)['value'] ?? null;
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
 * Resolve selectable options for a Zoho field.
 */
function resolveOptions(mixed $zohoField, Collection $localFields): array
{
    if ($zohoField->field_type === 'picklist') {
        return collect($zohoField->raw_metadata['pick_list_values'] ?? [])
            ->reject(fn ($opt): bool => ($opt['actual_value'] ?? null) === '-None-')
            ->map(fn ($opt): array => [
                'type' => 'zoho',
                'value' => $opt['actual_value'],
                'label' => $opt['display_value'],
            ])
            ->values()
            ->all();
    }

    return $localFields->map(fn ($local): array => [
        'type' => 'local',
        'value' => $local->id,
        'label' => $local->display_name,
    ])->values()->all();
}

/**
 * Resolve selected mapping for a Zoho field.
 */
function resolveSelected(?ZohoFieldMappings $mapping): ?array
{
    if (! $mapping instanceof \App\Plugins\Zoho\Models\ZohoFieldMappings) {
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
