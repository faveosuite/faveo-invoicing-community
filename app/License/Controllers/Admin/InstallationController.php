<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Models\LicensePlugin;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class InstallationController extends Controller
{
    public function __construct()
    {
        $this->ip_address = request()->server('REMOTE_ADDR');
    }

    public function installationUpdate(Request $request)
    {
        $id = $request->get('id');
        $installation = Installation::with('product:id,name')->find($id);

        if (! $installation || ! LicenseHelper::validateIntegerValue($id)) {
            return errorResponse(Lang::get('license::lang.invalid'), 400);
        }

        if ($request->get('delete_record') == 1) {
            $removed = $this->deleteInstallation($id);

            return response()->json([
                'api_action_success' => 1,
                'api_error_detected' => 0,
                'action_success' => $removed > 0 ? 1 : 0,
                'error_detected' => $removed > 0 ? 0 : 1,
                'page_message' => $removed > 0 ? "Deleted {$removed} installation(s)." : 'Invalid record or database error.',
            ]);
        }

        if (! filter_var($request->get('installation_ip'), FILTER_VALIDATE_IP) || ! LicenseHelper::validateIntegerValue($request->get('installation_status'), 0, 2)) {
            return response()->json([
                'api_action_success' => 1,
                'api_error_detected' => 0,
                'action_success' => 0,
                'error_detected' => 1,
                'page_message' => 'Installation could not be updated because of this reason: Invalid IP address or status.',
            ]);
        }

        $installation->update([
            'installation_ip' => $request->get('installation_ip'),
            'installation_disable_ip_verification' => $request->get('installation_disable_ip'),
            'installation_status' => $request->get('installation_status'),
        ]);

        $name = $installation->product?->name;
        $pageMessage = "{$name} installation on {$installation->installation_domain} ({$installation->installation_ip}) updated.";
        LicenseHelper::logAdminReport(strip_tags($pageMessage), 0, 1, 1);

        return response()->json([
            'api_action_success' => 1,
            'api_error_detected' => 0,
            'action_success' => 1,
            'error_detected' => 0,
            'page_message' => $pageMessage,
        ]);
    }

    private function deleteInstallation($id)
    {
        $installation = Installation::find($id);
        if (! $installation) {
            return 0;
        }

        $licenseId = License::where('license_code', $installation->license_code)->value('id');
        $pluginProductIds = LicensePlugin::where('license_id', $licenseId)->pluck('product_id');

        return Installation::where('license_code', $installation->license_code)
            ->where(function ($query) use ($id, $pluginProductIds): void {
                $query->where('id', $id)->orWhereIn('product_id', $pluginProductIds);
            })
            ->delete();
    }

    public function show(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = strtolower((string) $request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortField = in_array($request->input('sort_field', 'id'), ['id', 'product_id', 'user_id', 'license_code', 'installation_ip', 'installation_domain', 'installation_date', 'installation_status'], true) ? $request->input('sort_field', 'id') : 'id';

        $installations = Installation::query()
            ->with(['product:id,name', 'user:id,email', 'license:id,license_code'])
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function (Builder $q) use ($searchQuery): void {
                    $q->whereHas('user', fn (Builder $u) => $u->where('email', 'like', '%'.$searchQuery.'%'))
                        ->orWhereHas('product', fn (Builder $p) => $p->where('name', 'like', '%'.$searchQuery.'%'))
                        ->orWhere('license_code', 'like', '%'.str_replace('-', '', $searchQuery).'%')
                        ->orWhere('installation_ip', 'like', '%'.$searchQuery.'%')
                        ->orWhere('installation_status', 'like', '%'.LicenseHelper::statusFormatter($searchQuery).'%')
                        ->orWhere('installation_domain', 'like', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $installations->getCollection()->transform(fn (Installation $installation) => [
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
        ]);

        return successResponse(Lang::get('license::lang.Install_show'), $installations);
    }

    public function installationAdd(Request $request)
    {
        $license = License::where('license_code', $request->get('license_code'))->first();
        if (! $license) {
            return errorResponse(Lang::get('license::lang.invalid_licnese'), 404);
        }

        $installation = Installation::create([
            'license_code' => $license->license_code,
            'product_id' => $license->product_id,
            'user_id' => $license->user_id,
            'installation_ip' => request()->server('REMOTE_ADDR') ?: $request->ip(),
            'installation_domain' => $request->get('installation_domain'),
            'installation_date' => $request->get('installation_date') ?: now(),
            'installation_status' => $request->get('installation_status'),
            'installation_hash' => $request->get('installation_hash'),
        ]);

        return successResponse(Lang::get('license::lang.install_added'), $installation, 200);
    }

    public function edit($id)
    {
        $installation = Installation::findOrFail($id);

        return successResponse('', ['installation' => $installation], 200);
    }

    public function removeUnwantedInstallations(Request $request)
    {
        return Installation::where('installation_domain', $request->installation_path)->delete();
    }

    public function updateTheLicenseCode(Request $request)
    {
        return Installation::where('license_code', $request->old_license_code)->delete();
    }

    public function deleteInstallations(Request $request)
    {
        $id = $request->input('id');
        $removed = LicenseHelper::validateIntegerValue($id) ? Installation::where('id', $id)->delete() : 0;

        return successResponse(Lang::get('license::lang.installation_delete'), $removed);
    }
}
