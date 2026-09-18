<?php

namespace App\Plugins\Zoho\Helpers;

use App\Plugins\Zoho\Models\FaveoLocalFields;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ZohoConnectHelper
{
    public static function getModulesFields(string $platform, string $module): mixed
    {
        return ZohoFields::wherePlatform($platform)
            ->whereModule($module)
            ->get()
            // Zoho marks some fields (Record Id, Locked, system change-log timestamps, ...)
            // read-only. They'll always be rejected/ignored if pushed, so don't offer them.
            ->reject(fn ($z): bool => (bool) (($z->raw_metadata ?? [])['field_read_only'] ?? false))
            ->map(fn ($z): array => [
                'id' => $z->id,
                'field_name' => $z->display_name,
                'type' => $z->field_type,
            ])
            ->values();
    }

    /**
     * Get only existing mappings (CRM style).
     */
    public static function getExistingMappings(string $platform, string $module): mixed
    {
        $mappings = ZohoFieldMappings::with(['faveoLocalField', 'zohoField'])
            ->whereHas('zohoField', function (Builder $query) use ($platform, $module): void {
                $query->where('module', $module)
                    ->where('platform', $platform);
            })
            ->get();

        return $mappings->map(function ($mapping): ?array {
            // Check for Zoho option mapping
            if ($mapping->selected_option) {
                return [
                    'zoho_field_id' => $mapping->zoho_field_id,
                    'selected' => [
                        'type' => 'zoho',
                        'value' => json_decode((string) $mapping->selected_option, associative: true)['value'] ?? null,
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

    /**
     * @param  Collection<int|string, mixed>  $localFields
     * @param  Collection<int|string, mixed>  $zohoFields
     *
     * @phpstan-param Collection<array-key, mixed> $zohoFields
     * @phpstan-param Collection<array-key, mixed> $localFields
     */
    public static function mergeFields(Collection $zohoFields, Collection $localFields): mixed
    {
        $mappings = ZohoFieldMappings::with('faveoLocalField')
            ->get()
            ->keyBy('zoho_field_id');

        $localFieldOptions = $localFields->map(fn ($local): array => [
            'id' => $local->id,
            'type' => 'local_field',
            'label' => $local->display_name,
        ])->values();

        return $zohoFields->map(function ($zoho) use ($mappings, $localFieldOptions): array {
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
                        'id' => sprintf('zoho_%s_%s', $zoho->id, $index), // @phpstan-ignore property.notFound
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
                    'option_ids' => $mapping->selected_option ?? [],
                ],
            ];
        })->values();
    }

    /**
     * Zoho field types that can never safely take an arbitrary local-field
     * value, regardless of the local field's own type — verified live
     * against a real connected account:
     *  - `owner` expects a Zoho user id, not a plain string.
     *  - `lookup` expects an existing linked record id; a plain string is
     *    silently accepted and auto-creates a *new* duplicate record instead
     *    of erroring (confirmed: mapping a lookup field to a local field
     *    spawned a phantom Account with the string as its name).
     *  - `image` expects a file upload, not a field value.
     *  - `datetime` fields exposed by Zoho's standard modules are, in
     *    practice, always its own audit timestamps (Created/Modified/etc.) —
     *    writes to them are silently ignored even when not flagged
     *    read-only. Separately, the one local `datetime`-typed value this
     *    app has (Carbon's default JSON serialization) doesn't match what a
     *    Zoho `date` field expects either (confirmed: rejected as
     *    INVALID_DATA), so there's no local type worth allowing here.
     */
    private const array LOCAL_MAPPING_BLOCKED_TYPES = ['owner', 'lookup', 'image', 'datetime'];

    /**
     * lang/en/message.php key for each always-blocked Zoho field type's
     * explanation, shown to the admin configuring the mapping. See
     * LOCAL_MAPPING_BLOCKED_TYPES's doc for the evidence behind each one.
     *
     * @var array<string, string>
     */
    private const array LOCAL_MAPPING_BLOCKED_LANG_KEYS = [
        'owner' => 'message.zoho_mapping_owner_blocked',
        'lookup' => 'message.zoho_mapping_lookup_blocked',
        'image' => 'message.zoho_mapping_image_blocked',
        'datetime' => 'message.zoho_mapping_datetime_blocked',
    ];

    /**
     * Which local field types are safe to send into each Zoho field type —
     * verified live. Zoho validates each field's data type strictly and
     * rejects the *entire* record on a mismatch, not just that one field, so
     * an untested pairing is treated as unsafe by default: a Zoho field type
     * with no entry here (or a local type not listed for it) is rejected.
     *
     * @var array<string, list<string>>
     */
    private const array TYPE_COMPATIBILITY = [
        'text' => ['text', 'textarea', 'email', 'phone', 'string'],
        'textarea' => ['text', 'textarea', 'email', 'phone', 'string'],
        'email' => ['text', 'textarea', 'email', 'phone', 'string'],
        'phone' => ['text', 'textarea', 'email', 'phone', 'string'],
        'number' => ['number', 'decimal', 'integer', 'float'],
        'decimal' => ['number', 'decimal', 'integer', 'float'],
        'checkbox' => ['boolean', 'checkbox'],
        'date' => ['date'],
    ];

    /**
     * lang/en/message.php key for a field type's plain-language description,
     * for composing a sentence an admin can actually read. Falls back to the
     * raw type name for anything not listed (a custom/unrecognized type).
     *
     * @var array<string, string>
     */
    private const array TYPE_DESCRIPTION_LANG_KEYS = [
        'text' => 'message.zoho_field_type_text',
        'textarea' => 'message.zoho_field_type_text',
        'string' => 'message.zoho_field_type_text',
        'email' => 'message.zoho_field_type_email',
        'phone' => 'message.zoho_field_type_phone',
        'number' => 'message.zoho_field_type_number',
        'decimal' => 'message.zoho_field_type_number',
        'integer' => 'message.zoho_field_type_number',
        'float' => 'message.zoho_field_type_number',
        'checkbox' => 'message.zoho_field_type_checkbox',
        'boolean' => 'message.zoho_field_type_checkbox',
        'date' => 'message.zoho_field_type_date',
        'datetime' => 'message.zoho_field_type_datetime',
    ];

    /**
     * Validate every 'local' mapping in a save request against Zoho's field
     * type, before anything is persisted. Called by ZohoBaseController::updateMapping()
     * ahead of its transaction, so an incompatible pairing never reaches the DB.
     *
     * @param  array<int, array{zoho_field_id?: mixed, selected?: array{type?: mixed, value?: mixed}}>  $mappings
     * @return string|null the first incompatibility found (human-readable), or null when every mapping is safe
     */
    public static function findIncompatibleMapping(array $mappings): ?string
    {
        foreach ($mappings as $map) {
            if (($map['selected']['type'] ?? null) !== 'local') {
                continue;
            }

            $zohoField = ZohoFields::find($map['zoho_field_id'] ?? null);
            $localField = FaveoLocalFields::find($map['selected']['value'] ?? null);

            if (! $zohoField instanceof ZohoFields || ! $localField instanceof FaveoLocalFields) {
                continue;
            }

            $reason = self::incompatibleLocalMappingReason($zohoField, $localField);

            if ($reason !== null) {
                return $reason;
            }
        }

        return null;
    }

    /**
     * Whether mapping $zohoField to $localField is safe. Returns null when
     * it's fine, or a human-readable reason when it isn't.
     */
    private static function incompatibleLocalMappingReason(ZohoFields $zohoField, FaveoLocalFields $localField): ?string
    {
        // A missing field_type is treated as an unrecognized type, same as
        // any other — it won't match picklist/blocked/compatible below, so
        // it falls through to the mismatch case (unknown = unsafe).
        $zohoType = $zohoField->field_type ?? '';
        $localType = $localField->field_type ?? '';

        if ($zohoType === 'picklist') {
            // A picklist with Zoho's own defined choices has a safe option
            // already (map it to one of those instead) — only fall back to
            // allowing a local field when Zoho gives us no choices to pick.
            if (zohoPicklistOptions($zohoField)->isNotEmpty()) {
                return __('message.zoho_mapping_picklist_has_options', ['field' => $zohoField->display_name]);
            }

            return null;
        }

        if (in_array($zohoType, self::LOCAL_MAPPING_BLOCKED_TYPES, true)) {
            return __(self::LOCAL_MAPPING_BLOCKED_LANG_KEYS[$zohoType], ['field' => $zohoField->display_name]);
        }

        $allowedLocalTypes = self::TYPE_COMPATIBILITY[$zohoType] ?? [];

        if (! in_array($localType, $allowedLocalTypes, true)) {
            return __('message.zoho_mapping_type_mismatch', [
                'field' => $zohoField->display_name,
                'zoho_type' => self::describeType($zohoType),
                'local_field' => $localField->display_name,
                'local_type' => self::describeType($localType),
            ]);
        }

        return null;
    }

    private static function describeType(string $type): string
    {
        $langKey = self::TYPE_DESCRIPTION_LANG_KEYS[$type] ?? null;

        return $langKey !== null ? __($langKey) : $type;
    }

    /**
     * @param  array<mixed>  $meta
     * @param  array<mixed>  $selected
     */
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
