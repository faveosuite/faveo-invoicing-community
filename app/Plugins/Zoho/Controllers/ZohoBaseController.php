<?php

namespace App\Plugins\Zoho\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Zoho\Helpers\ConnectHelper;
use App\Plugins\Zoho\Models\FaveoLocalFields;
use App\Plugins\Zoho\Models\ZohoFields;
use Illuminate\Http\Request;

class ZohoBaseController extends Controller
{
    /**
     * Get mapped fields for a specific platform and module.
     */
    public function getModulesFields(string $platform, string $module)
    {
        $moduleFields = ConnectHelper::getModulesFields($platform, $module);

        return successResponse('', $moduleFields);
    }


    public function getMappedFields(string $platform, string $module)
    {
        $mappedFields = ConnectHelper::getExistingMappings($platform, $module);

        return successResponse('', $mappedFields);
    }

    /**
     * Update mapping for a specific field.
     */
    public function updateMapping(Request $request)
    {
        $request->validate([
            'mappings' => 'required|array',
            'mappings.*.zoho_field_id' => 'required|exists:zoho_fields,id',
            'mappings.*.selected.type' => 'required|in:local,zoho',
            'mappings.*.selected.value' => 'required',
        ]);

        \DB::transaction(function () use ($request) {
            foreach ($request->mappings as $map) {
                ConnectHelper::updateMapping(
                    $map['zoho_field_id'],
                    $map['selected'],
                    $map
                );
            }
        });

        return successResponse('Zoho field mapping updated successfully');
    }

    /**
     * Get options for a specific Zoho field.
     */
    public function getOptions($zohoFieldID)
    {
        $localFields = FaveoLocalFields::get();

        $zohoFields = ZohoFields::find($zohoFieldID);

        return resolveOptions($zohoFields, $localFields);
    }
}
