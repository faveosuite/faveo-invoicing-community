<?php

namespace App\License\Controllers\Admin\Views;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\Installation;
use App\License\Models\LicenseCallback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class InstallationViewController extends Controller
{
    public function getInstallation($id)
    {
        $installation = Installation::with(['product:id,name', 'user:id,email', 'license:id,license_code'])
            ->find($id);

        if ($installation) {
            $installation = [
                'id' => $installation->id,
                'product_id' => $installation->product_id,
                'client_id' => $installation->user_id,
                'license_code' => $installation->license_code,
                'installation_ip' => $installation->installation_ip,
                'installation_domain' => $installation->installation_domain,
                'installation_date' => $installation->installation_date,
                'installation_status' => $installation->installation_status,
                'product_title' => $installation->product?->name,
                'client_email' => $installation->user?->email,
                'license_id' => $installation->license?->id,
            ];
        }

        return successResponse(Lang::get('lang.installation_details'), $installation);
    }

    public function getInstallationCallbacks(Request $request, $id)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');
        $installationDomain = Installation::where('id', $id)->value('installation_domain');
        $callbacks = LicenseCallback::where('callback_domain', $installationDomain)
            ->select('id', 'callback_ip', 'callback_domain', 'callback_date_time', 'callback_status')
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function ($query) use ($searchQuery): void {
                    $query->where('callback_ip', 'like', '%'.$searchQuery.'%')
                        ->orWhere('callback_domain', 'like', '%'.$searchQuery.'%')
                        ->orWhere('callback_status', 'LIKE', '%'.LicenseHelper::statusFormatter($searchQuery).'%')
                        ->orWhere('callback_date_time', 'like', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $callbacks->getCollection()->transform(fn (LicenseCallback $cb): array => [
            'id' => $cb->id,
            'callback_ip' => $cb->callback_ip,
            'callback_domain' => $cb->callback_domain,
            'callback_date_time' => $cb->callback_date_time,
            'callback_status' => $cb->callback_status,
        ]);

        return successResponse(Lang::get('lang.installation_callbacks'), $callbacks);
    }
}
