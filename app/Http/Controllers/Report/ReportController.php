<?php

namespace App\Http\Controllers\Report;

use App\ExportDetail;
use App\Http\Controllers\Controller;
use App\ReportSetting;
use DB;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function getAllReports(Request $request): JsonResponse
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = $request->input('sort-order', 'asc');
        $sortField = $request->input('sort-field', 'created_at');
        $limit = $request->input('limit', 10);

        // 'format' and 'type' are PHP-computed below (from 'file' and 'name'), not real columns —
        // sort by their source column instead.
        $columnMap = ['format' => 'file', 'type' => 'name'];
        $allowed = ['file', 'format', 'type', 'created_at'];
        if (! in_array($sortField, $allowed, true)) {
            $sortField = 'created_at';
        }
        $sortField = $columnMap[$sortField] ?? $sortField;

        $reports = ExportDetail::with(['user:id,first_name,last_name'])
            ->where('user_id', auth()->id())
            ->when($searchQuery, function ($query) use ($searchQuery): void {
                $query->where(function (Builder $q) use ($searchQuery): void {
                    $q->whereHas('user', function (Builder $q2) use ($searchQuery): void {
                        $q2->where('first_name', 'like', sprintf('%%%s%%', $searchQuery))
                            ->orWhere('last_name', 'like', sprintf('%%%s%%', $searchQuery));
                    })
                        ->orWhere('file', 'like', sprintf('%%%s%%', $searchQuery));
                });
            })
            ->orderBy($sortField, $sortOrder)
            ->paginate($limit);

        $reports->getCollection()->transform(function ($report): array {
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

    public function deleteBulkReports(Request $request): JsonResponse
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
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getReportsSettings(Request $request): JsonResponse
    {
        return successResponse('', ReportSetting::first());
    }

    public function updateReportsSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => ['required', 'integer', 'min:1', 'max:3000'],
        ]);

        $settings = ReportSetting::firstOrFail();

        $settings->update([
            'records' => $validated['records'],
        ]);

        return successResponse(__('message.settings_updated_successfully'));
    }
}
