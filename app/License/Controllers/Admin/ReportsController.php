<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Helpers\LicenseHelper;
use App\License\Models\License;
use App\License\Models\LicenseReport;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class ReportsController extends Controller
{
    public string $error_details = '';

    public int $removed_records = 0;

    public int $action_success = 1;

    public function reports(Request $request): \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
    {
        $report_ids_array = $request->arr;
        $whichReport = $request->get('which_report');
        if (! empty($report_ids_array)) {
            foreach ($report_ids_array as $report_id) {
                $this->removed_records += (int) $this->deleteReport($report_id, $this->removed_records);
            }

            if (! LicenseHelper::validateIntegerValue($this->removed_records)) {
                $this->action_success = 0;
                $this->error_details .= Lang::get('lang.inavalid_records');
            } else {
                $this->action_success = 1;
            }
        } else {
            $this->action_success = 0;
            $this->error_details .= Lang::get('lang.no_record_selected');
        }

        $page_message = $this->whichReportDeleted($whichReport, $this->action_success, $this->removed_records, $this->error_details);
        LicenseHelper::logAdminReport(strip_tags((string) $page_message), 1, 1, $this->action_success); // @phpstan-ignore cast.string

        return response(['message' => $page_message]);
    }

    /**
     * @return array<mixed>
     */
    protected function whichReportDeleted(mixed $whichReport, mixed $action_success, mixed $removed_records, mixed $error_details = ''): \Illuminate\Contracts\Translation\Translator|string|array
    {
        $page_message = '';
        if (! empty($whichReport)) {
            if ($action_success == 1) { // everything OK
                $page_message = trans('lang.Deleted', ['removed' => $removed_records, 'which' => $whichReport]);
            } else { // display error message
                $page_message = trans('lang.report_not_deleted', ['which' => $whichReport, 'error_details' => $error_details]);
            }
        }

        return $page_message;
    }

    //delete report
    private function deleteReport(mixed $report_id, int $removed_records): int|float
    {
        if (LicenseHelper::validateIntegerValue($report_id)) {
            $removed_records += LicenseReport::where('license_reports.id', $report_id)->delete();
        }

        return $removed_records;
    }

    //system1
    public function reportArraySystem(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        $sortOrder = strtolower((string) $sortOrder) === 'asc' ? 'asc' : 'desc';
        $sortField = in_array($sortField, ['id', 'user_id', 'license_code', 'report_text', 'report_date_time', 'report_status'], strict: true) ? $sortField : 'id';

        $reportsQuery = LicenseReport::query()
            ->with('user:id,email')
            ->where('report_system', 1)
            ->when($searchQuery, function ($query, $searchQuery): void {
                $query->where(function (Builder $query) use ($searchQuery): void {
                    $query->where('report_text', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('report_status', 'LIKE', '%'.$this->reportStatusFormatter($searchQuery).'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $reportsQuery->getCollection()->transform(fn (LicenseReport $report): array => [
            'id' => $report->id,
            'account_id' => $report->user_id,
            'license_code' => $report->license_code,
            'report_text' => $report->report_text,
            'report_date_time' => $report->report_date_time,
            'report_status' => $report->report_status,
            'user_formatted' => $report->user->email ?? 'System',
        ]);

        return successResponse(__('lang.SystemReport_Show'), $reportsQuery, 200);
    }

    public function reportArrayCracking(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        $sortOrder = strtolower((string) $sortOrder) === 'asc' ? 'asc' : 'desc';
        $sortField = in_array($sortField, ['id', 'user_id', 'license_code', 'report_text', 'report_date_time', 'report_status'], strict: true) ? $sortField : 'id';

        $crakingReports = LicenseReport::query()
            ->whereNull('user_id')
            ->whereNull('product_id')
            ->whereNull('license_code')
            ->where('report_system', 0)
            ->where('report_text', 'not like', '%upgrade%')
            ->where('report_text', 'not like', '%file_to_download%')
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function (Builder $query) use ($searchQuery): void {
                    $query->where('report_text', 'LIKE', '%'.$searchQuery.'%')
                        ->orWhere('report_status', 'LIKE', '%'.$this->reportStatusFormatter($searchQuery).'%')
                        ->orWhere('license_code', 'LIKE', '%'.str_replace('-', '', $searchQuery).'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $licenseIdsByCode = License::whereIn('license_code', $crakingReports->pluck('license_code')->filter()->unique())
            ->pluck('id', 'license_code');

        $crakingReports->getCollection()->transform(fn (LicenseReport $report): array => [
            'id' => $report->id,
            'user_id' => $report->user_id,
            'license_code' => $report->license_code,
            'report_text' => $report->report_text,
            'report_date_time' => $report->report_date_time,
            'report_status' => $report->report_status,
            'license_id' => $licenseIdsByCode[$report->license_code] ?? null,
        ]);

        return successResponse(__('lang.CrackingReport_Show'), $crakingReports, 200);
    }

    public function reportArrayLicense(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        $sortOrder = strtolower((string) $sortOrder) === 'asc' ? 'asc' : 'desc';
        $sortField = in_array($sortField, ['id', 'user_id', 'license_code', 'report_text', 'report_date_time', 'report_status'], strict: true) ? $sortField : 'id';

        $LicenseReports = LicenseReport::query()
            ->with('user:id,email')
            ->whereNotNull('license_code')
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function (Builder $query) use ($searchQuery): void {
                    $query->where('report_text', 'like', '%'.$searchQuery.'%')
                        ->orWhere('report_status', 'LIKE', '%'.$this->reportStatusFormatter($searchQuery).'%')
                        ->orWhereHas('user', function (Builder $userQuery) use ($searchQuery): void {
                            $userQuery->where('email', 'like', '%'.$searchQuery.'%');
                        })
                        ->orWhere('license_code', 'like', '%'.str_replace('-', '', $searchQuery).'%')
                        ->orWhere('report_date_time', 'like', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $licenseIdsByCode = License::whereIn('license_code', $LicenseReports->pluck('license_code')->filter()->unique())
            ->pluck('id', 'license_code');

        $LicenseReports->getCollection()->transform(fn (LicenseReport $report): array => [
            'id' => $report->id,
            'client_id' => $report->user_id,
            'report_text' => $report->report_text,
            'license_code' => $report->license_code,
            'report_date_time' => $report->report_date_time,
            'report_status' => $report->report_status,
            'client_email' => $report->user?->email,
            'license_id' => $licenseIdsByCode[$report->license_code] ?? null,
        ]);

        return successResponse(__('lang.LicenseReport_Show'), $LicenseReports, 200);
    }

    public function reportArrayUpdate(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = str_replace('-', '', $request->input('search_query'));
        $sortOrder = $request->input('sort_order', 'desc');
        $sortField = $request->input('sort_field', 'id');

        $sortOrder = strtolower((string) $sortOrder) === 'asc' ? 'asc' : 'desc';
        $sortField = in_array($sortField, ['id', 'user_id', 'product_id', 'report_text', 'report_date_time', 'report_status'], strict: true) ? $sortField : 'id';

        $updateReports = LicenseReport::query()
            ->with('product:id,name')
            ->where('report_text', 'like', '%upgrade%')
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function (Builder $query) use ($searchQuery): void {
                    $query->where('report_text', 'like', '%'.$searchQuery.'%')
                        ->orWhereHas('product', function (Builder $productQuery) use ($searchQuery): void {
                            $productQuery->where('name', 'like', '%'.$searchQuery.'%');
                        })
                        ->orWhere('report_status', 'LIKE', '%'.$this->reportStatusFormatter($searchQuery).'%')
                        ->orWhere('report_date_time', 'like', '%'.$searchQuery.'%');
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);

        $updateReports->getCollection()->transform(fn (LicenseReport $report): array => [
            'id' => $report->id,
            'user_id' => $report->user_id,
            'report_text' => $report->report_text,
            'report_date_time' => $report->report_date_time,
            'report_status' => $report->report_status,
            'product_title' => $report->product?->name,
            'product_id' => $report->product_id,
        ]);

        return successResponse(__('lang.report_update'), $updateReports, 200);
    }

    private function reportStatusFormatter(mixed $status): mixed
    {
        if (strtolower($status) === 'success') {
            $status = 'success';
        }

        if (strtolower($status) === 'error') {
            $status = 'error';
        }

        if (strtolower($status) === 'pending') {
            return 'pending';
        }

        return $status;
    }
}
