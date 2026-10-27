<?php

namespace App\Plugins\Zoho\Controllers;

use App\Plugins\Zoho\Helpers\ZohoConnectHelper;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use Exception;

class ZohoSync
{
    private const UNHANDLED_PLATFORM = 'Unhandled platform: ';

    /**
     * Fields are matched by name first, then by Zoho's id. The id differs
     * between Zoho accounts but the name doesn't, so reconnecting a different
     * account updates the existing fields (and keeps their mappings) instead
     * of duplicating them; the id still catches a field renamed in Zoho.
     *
     * @param  array<mixed>  $fields
     * @return list<string> Display names of fields whose mapping was removed because it no longer fits.
     */
    public function sync(string $platform, string $module, array $fields): array
    {
        $keys = [];

        foreach ($fields as $field) {
            $uid = $this->getFieldUid($platform, $field);
            $keys[] = $key = $this->getZohoKey($platform, $field);

            $existing = ZohoFields::wherePlatform($platform)->whereModule($module)->where('zoho_key', $key)->first()
                ?? ZohoFields::wherePlatform($platform)->whereModule($module)->where('zoho_field_uid', $uid)->first()
                ?? new ZohoFields(['platform' => $platform, 'module' => $module]);

            $existing->fill([
                'zoho_field_uid' => $uid,
                'zoho_key' => $key,
                'display_name' => $this->getDisplayName($platform, $field),
                'field_type' => $this->getFieldType($platform, $field),
                'is_mandatory' => $this->isMandatory($platform, $field),
                'raw_metadata' => $field,
            ])->save();
        }

        $removed = [];

        // Fields Zoho no longer has: drop them (their mapping goes with them).
        foreach (ZohoFields::with('mapping')->wherePlatform($platform)->whereModule($module)->whereNotIn('zoho_key', $keys)->get() as $gone) {
            if ($gone->mapping !== null) {
                $removed[] = $gone->display_name;
            }

            $gone->delete();
        }

        // Fields that changed (type, picklist choices): re-check their mapping with the same rule the mapping page uses on save.
        foreach (ZohoFieldMappings::with(['zohoField', 'faveoLocalField'])->whereHas('zohoField', fn ($q) => $q->wherePlatform($platform)->whereModule($module))->get() as $mapping) {
            if (! ZohoConnectHelper::mappingStillValid($mapping)) {
                $removed[] = $mapping->zohoField->display_name;
                $mapping->delete();
            }
        }

        return $removed;
    }

    /* ----------------------------------------------------
     | Normalizers
     |----------------------------------------------------*/

    /**
     * @param  array<mixed>  $field
     */
    protected function getFieldUid(string $platform, array $field): string
    {
        return match ($platform) {
            'crm' => (string) $field['id'],
            'campaigns' => (string) $field['FIELD_ID'],
            default => throw new Exception('Unsupported platform '.$platform)
        };
    }

    /**
     * @param  array<mixed>  $field
     */
    protected function getZohoKey(string $platform, array $field): string
    {
        return match ($platform) {
            'crm' => $field['api_name'],
            'campaigns' => $field['FIELD_NAME'],
            default => throw new \UnexpectedValueException(self::UNHANDLED_PLATFORM.$platform),
        };
    }

    /**
     * @param  array<mixed>  $field
     */
    protected function getDisplayName(string $platform, array $field): string
    {
        return match ($platform) {
            'crm' => $field['field_label'],
            'campaigns' => $field['DISPLAY_NAME'],
            default => throw new \UnexpectedValueException(self::UNHANDLED_PLATFORM.$platform),
        };
    }

    /**
     * ONE unified field type.
     *
     * @param  array<mixed>  $field
     */
    protected function getFieldType(string $platform, array $field): ?string
    {
        // Campaigns already gives readable types
        if ($platform === 'campaigns') {
            return strtolower($field['UITYPE'] ?? 'text');
        }

        return match (strtolower($field['data_type'] ?? 'text')) {
            'text' => 'text',
            'textarea' => 'textarea',
            'email' => 'email',
            'phone' => 'phone',
            'bigint' => 'number',
            'double' => 'decimal',
            'boolean' => 'checkbox',
            'date' => 'date',
            'datetime' => 'datetime',
            'picklist' => 'picklist',
            'lookup' => 'lookup',
            'ownerlookup' => 'owner',
            'profileimage' => 'image',
            default => 'text',
        };
    }

    /**
     * @param  array<mixed>  $field
     */
    protected function isMandatory(string $platform, array $field): bool
    {
        return match ($platform) {
            'crm' => (bool) ($field['system_mandatory'] ?? false),
            'campaigns' => (bool) ($field['IS_MANDATORY'] ?? false),
            default => throw new \UnexpectedValueException(self::UNHANDLED_PLATFORM.$platform),
        };
    }
}
