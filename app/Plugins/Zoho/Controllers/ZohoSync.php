<?php

namespace App\Plugins\Zoho\Controllers;

use App\Plugins\Zoho\Models\ZohoFields;
use Exception;

class ZohoSync
{
    /**
     * @param array<mixed> $fields
     */
    public function sync(string $platform, string $module, array $fields): void
    {
        foreach ($fields as $field) {
            ZohoFields::updateOrCreate(
                [
                    'platform' => $platform,
                    'module' => $module,
                    'zoho_field_uid' => $this->getFieldUid($platform, $field),
                ],
                [
                    'zoho_key' => $this->getZohoKey($platform, $field),
                    'display_name' => $this->getDisplayName($platform, $field),
                    'field_type' => $this->getFieldType($platform, $field),
                    'is_mandatory' => $this->isMandatory($platform, $field),
                    'raw_metadata' => $field,
                ]
            );
        }
    }

    /* ----------------------------------------------------
     | Normalizers
     |----------------------------------------------------*/

    /**
     * @param array<mixed> $field
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
     * @param array<mixed> $field
     */
    protected function getZohoKey(string $platform, array $field): string
    {
        return match ($platform) {
            'crm' => $field['api_name'],
            'campaigns' => $field['FIELD_NAME'],
            default => throw new \UnexpectedValueException('Unhandled platform: '.$platform),
        };
    }

    /**
     * @param array<mixed> $field
     */
    protected function getDisplayName(string $platform, array $field): string
    {
        return match ($platform) {
            'crm' => $field['field_label'],
            'campaigns' => $field['DISPLAY_NAME'],
            default => throw new \UnexpectedValueException('Unhandled platform: '.$platform),
        };
    }

    /**
     * ONE unified field type.
     * @param array<mixed> $field
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
     * @param array<mixed> $field
     */
    protected function isMandatory(string $platform, array $field): bool
    {
        return match ($platform) {
            'crm' => (bool) ($field['system_mandatory'] ?? false),
            'campaigns' => (bool) ($field['IS_MANDATORY'] ?? false),
            default => throw new \UnexpectedValueException('Unhandled platform: '.$platform),
        };
    }
}
