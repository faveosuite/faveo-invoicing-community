<?php

namespace App\Plugins\Zoho\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Zoho\Helpers\ZohoConnectHelper;
use App\Plugins\Zoho\Models\FaveoLocalFields;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use App\Plugins\Zoho\Models\ZohoIntegration;
use DB;
use Illuminate\Http\Request;

class ZohoBaseController extends Controller
{
    /**
     * Get mapped fields for a specific platform and module.
     */
    public function getModulesFields(string $platform, string $module)
    {
        $moduleFields = ZohoConnectHelper::getModulesFields($platform, $module);

        return successResponse('', $moduleFields);
    }

    public function getMappedFields(string $platform, string $module)
    {
        $mappedFields = ZohoConnectHelper::getExistingMappings($platform, $module);

        return successResponse('', $mappedFields);
    }

    /**
     * Update mapping for a specific field.
     */
    public function updateMapping(Request $request)
    {
        $request->validate([
            'integration_id' => ['required', 'exists:zoho_integrations,id'],
            'module' => ['required', 'string'],
            'mappings' => ['required', 'array'],
            'mappings.*.zoho_field_id' => ['required', 'exists:zoho_fields,id'],
            'mappings.*.selected.type' => ['required', 'in:local,zoho'],
            'mappings.*.selected.value' => ['required'],
        ]);

        DB::transaction(function () use ($request): void {
            $incomingIds = collect($request->mappings)
                ->pluck('zoho_field_id')
                ->unique();

            $zohoIntegration = ZohoIntegration::findOrFail($request->integration_id);

            ZohoFieldMappings::whereIn('zoho_field_id', function ($query) use (
                $request,
                $zohoIntegration,
                $incomingIds
            ): void {
                $query->select('id')
                    ->from('zoho_fields')
                    ->where('module', $request->module)
                    ->where('platform', $zohoIntegration->platform)
                    ->whereNotIn('id', $incomingIds);
            })->delete();

            foreach ($request->mappings as $map) {
                ZohoConnectHelper::updateMapping(
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
