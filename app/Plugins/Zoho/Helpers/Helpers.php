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

        $zohoKey = $zohoField->platform === 'campaigns' ?
            $zohoField->display_name:
            $zohoField->zoho_key;

        $selected = resolveSelected($zohoField, $mapping);

        if ($selected['type'] === 'local') {
            $updateKey = $mapping->faveoLocalField->field_key;
            $value = data_get($source, $updateKey) ?? $mapping->default_value;
        }

        if ($selected['type'] === 'zoho') {
            $value = json_decode($selected['value'], true)['value'] ?? null;
        }

        if ($value === null || $value === '') {
            continue;
        }

        $result[$zohoKey] = $value;
    }

    return $result;
}

/**
 * Resolve selectable options for a Zoho field.
 */
function resolveOptions($zohoField, Collection $localFields): array
{
    if ($zohoField->field_type === 'picklist') {
        return collect($zohoField->raw_metadata['pick_list_values'] ?? [])
            ->reject(fn ($opt) => ($opt['actual_value'] ?? null) === '-None-')
            ->map(fn ($opt) => [
                'type' => 'zoho',
                'value' => $opt['actual_value'],
                'label' => $opt['display_value'],
            ])
            ->values()
            ->all();
    }

    return $localFields->map(fn ($local) => [
        'type' => 'local',
        'value' => $local->id,
        'label' => $local->display_name,
    ])->values()->all();
}

/**
 * Resolve selected mapping for a Zoho field.
 */
function resolveSelected($zohoField, ?ZohoFieldMappings $mapping): ?array
{
    if (! $mapping) {
        return null;
    }

    if ($zohoField->field_type === 'picklist') {
        return [
            'type' => 'zoho',
            'value' => $mapping->selected_option,
        ];
    }

    return [
        'type' => 'local',
        'value' => $mapping->faveo_local_field_id,
    ];
}

function getZohoRegion(string $region): ZohoRegion
{
    $default = config('zoho.default_region');

    return ZohoRegion::tryFrom($region)
        ?? ZohoRegion::tryFrom($default)
        ?? ZohoRegion::India;
}
