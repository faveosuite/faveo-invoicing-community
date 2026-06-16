<?php

namespace App\Http\Controllers\Report;

use Illuminate\Contracts\Database\Query\Builder;
use App\ExportDetail;
use App\Http\Controllers\Controller;
use App\ReportSetting;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Lang;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function viewReports()
    {
        return view('themes.default1.report.index');
    }

    public function destroyReports(Request $request)
    {
        $ids = $request->input('select');
        if (! empty($ids)) {
            foreach ($ids as $id) {
                $report = ExportDetail::where('id', $id)->first();
                if ($report) {
                    if (file_exists($report->file_path)) {
                        $relativeFilePath = str_replace(storage_path('app/'), '', $report->file_path);
                        Storage::delete($relativeFilePath);
                    }

                    $report->delete();
                } else {
                    echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */Lang::get('message.no-record').'
                </div>';
                    //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                }
            }

            echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */Lang::get('message.deleted-successfully').'
                </div>';
        } else {
            echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */ Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */ Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */Lang::get('message.select-a-row').'
                </div>';
        }
    }

    public function viewRecordsColumn()
    {
        $settings = ReportSetting::first();

        return view('themes.default1.report.records-per-col', compact('settings'));
    }

    public function addRecords(Request $request)
    {
        $request->validate([
            'records' => ['required', 'integer', 'min:1', 'max:3000'],
        ]);
        $settings = ReportSetting::first();
        $settings->records = $request->records;
        $settings->save();

        return back()->with('success', __('message.settings_updated_successfully'));
    }

    public function getAllReports(Request $request)
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = $request->input('sort-order', 'asc');
        $sortField = $request->input('sort-field', 'created_at');
        $limit = $request->input('limit', 10);

        $reports = ExportDetail::with(['user:id,first_name,last_name'])
            ->where('user_id', auth()->id())
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function (Builder $q) use ($searchQuery): void {
                    $q->whereHas('user', function (Builder $q2) use ($searchQuery): void {
                        $q2->where('first_name', 'like', "%{$searchQuery}%")
                            ->orWhere('last_name', 'like', "%{$searchQuery}%");
                    })
                        ->orWhere('file', 'like', "%{$searchQuery}%");
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->simplePaginate($limit);

        $reports->getCollection()->transform(function ($report) {
            $fileType = strtoupper(pathinfo((string) $report->file, PATHINFO_EXTENSION)) ?: 'XLSX';
            $type = $report->name ? ucfirst((string) $report->name).' Report' : 'Report';

            return [
                'id' => $report->id,
                'file' => $report->file,
                'format' => $fileType,
                'type' => $type,
                'user' => $report->user,
                'created_at' => $report->created_at,
            ];
        });

        return successResponse('', $reports);
    }

    public function deleteBulkReports(Request $request)
    {
        $ids = $request->input('select', []);

        if (empty($ids)) {
            return errorResponse(__('message.select-a-row'));
        }

        try {
            DB::transaction(function () use ($ids): void {
                $reports = ExportDetail::where('user_id', auth()->id())
                    ->whereIn('id', $ids)->get();

                foreach ($reports as $report) {
                    if (file_exists($report->file_path)) {
                        $relativeFilePath = str_replace(storage_path('app/'), '', $report->file_path);
                        Storage::delete($relativeFilePath);
                    }

                    $report->delete();
                }
            });

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getReportsSettings(Request $request)
    {
        return successResponse('', ReportSetting::first());
    }

    public function updateReportsSettings(Request $request)
    {
        $validated = $request->validate([
            'records' => ['required', 'integer', 'min:1', 'max:3000'],
        ]);

        $settings = ReportSetting::first();

        $settings->update([
            'records' => $validated['records'],
        ]);

        return successResponse(__('message.settings_updated_successfully'));
    }
}
