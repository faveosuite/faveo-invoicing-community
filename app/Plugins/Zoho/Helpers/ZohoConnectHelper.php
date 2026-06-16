<?php

namespace App\Plugins\Zoho\Helpers;

use Illuminate\Contracts\Database\Query\Builder;
use InvalidArgumentException;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use Illuminate\Support\Collection;

class ZohoConnectHelper
{
    public static function getModulesFields(string $platform, string $module)
    {
        return ZohoFields::wherePlatform($platform)
           ->whereModule($module)
           ->get()
           ->map(fn ($z) => [
               'id' => $z->id,
               'field_name' => $z->display_name,
               'type' => $z->field_type,
           ]);
    }

    /**
     * Get only existing mappings (CRM style).
     */
    public static function getExistingMappings(string $platform, string $module)
    {
        $mappings = ZohoFieldMappings::with(['faveoLocalField', 'zohoField'])
            ->whereHas('zohoField', function (Builder $query) use ($platform, $module): void {
                $query->where('module', $module)
                    ->where('platform', $platform);
            })
            ->get();

        return $mappings->map(function ($mapping) {
            // Check for Zoho option mapping
            if ($mapping->selected_option) {
                return [
                    'zoho_field_id' => $mapping->zoho_field_id,
                    'selected' => [
                        'type' => 'zoho',
                        'value' => $mapping->selected_option,
                    ],
                ];
            }

            // Check for Local field mapping
            if ($mapping->faveo_local_field_id) {
                return [
                    'zoho_field_id' => $mapping->zoho_field_id,
                    'selected' => [
                        'type' => 'local',
                        'value' => $mapping->faveo_local_field_id,
                    ],
                ];
            }

            return null;
        })
        ->filter()
        ->values();
    }

    public static function mergeFields(Collection $zohoFields, Collection $localFields)
    {
        $mappings = ZohoFieldMappings::with('faveoLocalField')
            ->get()
            ->keyBy('zoho_field_id');

        $localFieldOptions = $localFields->map(fn ($local) => [
            'id' => $local->id,
            'type' => 'local_field',
            'label' => $local->display_name,
        ])->values();

        return $zohoFields->map(function ($zoho) use ($mappings, $localFieldOptions) {
            $mapping = $mappings->get($zoho->id);

            $options = collect();

            // Local fields only if Zoho field has no own options
            if (empty($zoho->options)) {
                $options = $options->merge($localFieldOptions);
            }

            // Zoho picklist / multiselect options
            if (! empty($zoho->options)) {
                foreach ($zoho->options as $index => $label) {
                    $options->push([
                        'id' => "zoho_{$zoho->id}_{$index}",
                        'type' => 'zoho_option',
                        'label' => $label,
                    ]);
                }
            }

            return [
                'zoho' => [
                    'id' => $zoho->id,
                    'api_name' => $zoho->zoho_api_name ?? $zoho->zoho_key,
                    'label' => $zoho->display_name,
                    'allows_zoho_multi' => (bool) ($zoho->allows_zoho_multi ?? false),
                ],

                'options' => $options->values(),

                'selected' => [
                    'local_field_id' => $mapping?->faveo_local_field_id,
                    'option_ids' => $mapping?->selected_option ?? [],
                ],
            ];
        })->values();
    }

    public static function updateMapping(
        int $zohoFieldId,
        array $selected,
        array $meta = []
    ): void {
        $data = [
            'default_value' => $meta['default_value'] ?? null,
            'use_default_if_empty' => $meta['use_default_if_empty'] ?? false,
            'is_active' => $meta['is_active'] ?? true,
            'faveo_local_field_id' => null,
            'selected_option' => null,
            'option_mapping' => null,
        ];

        match ($selected['type'] ?? null) {
            'local' => $data['faveo_local_field_id'] = $selected['value'],
            'zoho' => $data['selected_option'] = json_encode([
                'value' => $selected['value'],
            ]),
            default => throw new InvalidArgumentException('Invalid selected type'),
        };

        ZohoFieldMappings::updateOrCreate(
            ['zoho_field_id' => $zohoFieldId],
            $data
        );
    }
}
