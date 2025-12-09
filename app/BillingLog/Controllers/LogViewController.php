<?php

namespace App\BillingLog\Controllers;

use App\BillingLog\Model\CronLog;
use App\BillingLog\Model\ExceptionLog;
use App\BillingLog\Model\LogCategory;
use App\BillingLog\Model\MailLog;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Spatie\Activitylog\Models\Activity;

class LogViewController
{
    private $searchString;
    private $sortOrder;
    private $sortField;
    private $limit;

    public function getSystemLogs()
    {
        return view('log::index');
    }

    public function getLogs($type, Request $request)
    {
        // Extract search/sorting/limit parameters once
        $this->applyListFiltersForLogs($request);

        switch($type) {
            case 'exception':
                return $this->getExceptionLogs($request);
            case 'cron':
                return $this->getCronLogs($request);
            case 'mail':
                return $this->getMailLogs($request);
        }
    }

    public function getExceptionLogs(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'category' => 'required|exists:log_categories,id',
        ]);

        try {
            $date = $request->input('date');
            $logCategoryId = $request->input('category');

            $exceptionCategory = LogCategory::find($logCategoryId);

            if (!$exceptionCategory) {
                return errorResponse(__('message.record_not_found'), 404);
            }

            $exceptionLog = $exceptionCategory->exceptions()
                ->whereDate('created_at', $date)
                ->when($this->searchString, function ($q) {
                    $search = $this->searchString;
                    $q->where(function ($q) use ($search) {
                        $q->where('message', 'like', "%$search%")
                            ->orWhere('file', 'like', "%$search%");
                    });
                })
                ->orderBy($this->sortField, $this->sortOrder)
                ->simplePaginate($this->limit);

            return successResponse( __('message.exceptions_fetched_successfully'), $exceptionLog);

        } catch (\Exception $e) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function getCronLogs(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'status' => 'in:completed,failed',
            'category' => 'required',
        ]);

        try {
            $date = $request->input('date');
            $status = $request->input('status');
            $cronCategory = $request->input('category');

            $cronLogs = CronLog::whereDate('created_at', $date)
                ->where('command', $cronCategory)
                ->when($status, fn($q) => $q->where('status', $status))
                ->when($this->searchString, function ($q) {
                    $search = $this->searchString;
                    $q->where(function ($q) use ($search) {
                        $q->where('description', 'like', "%$search%")
                            ->orWhere('command', 'like', "%$search%");
                    });
                })
                ->orderBy($this->sortField, $this->sortOrder)
                ->simplePaginate($this->limit);

            return successResponse(__('message.crons_fetched_successfully'), $cronLogs);

        } catch (\Exception $e) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }


    public function getMailLogs(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'category' => 'required|exists:log_categories,id',
            'status' => 'in:sent,failed,queued',
        ]);

        try {
            $date = $request->input('date');
            $logCategoryId = $request->input('category');
            $status = $request->input('status');

            $mailCategory = LogCategory::find($logCategoryId);

            if (!$mailCategory) {
                return errorResponse(__('message.record_not_found'), 404);
            }

            $mailLogs = $mailCategory->mail()
                ->whereDate('created_at', $date)
                ->when($status, fn($q) => $q->where('status', $status))
                ->when($this->searchString, function ($q) {
                    $search = $this->searchString;
                    $q->where(function ($sub) use ($search) {
                        $sub->where('sender_mail', 'like', "%$search%")
                            ->orWhere('receiver_mail', 'like', "%$search%")
                            ->orWhere('carbon_copy', 'like', "%$search%");
                    });
                })
                ->orderBy($this->sortField, $this->sortOrder)
                ->simplePaginate($this->limit);

            return successResponse(__('message.mail_logs_fetched_successfully'), $mailLogs);

        } catch (\Exception $e) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }


    public function deleteLogsByDate(array $logTypes, $date = null)
    {
        $logModels = [
            'cron' => CronLog::class,
            'exception' => ExceptionLog::class,
            'mail' => MailLog::class,
            'systemLogs' => Activity::class,
            'failed_jobs' => 'failed_jobs',
        ];

        foreach ($logTypes as $type) {
            if (! isset($logModels[$type])) {
                continue;
            }

            if ($type === 'failed_jobs') {
                $query = \DB::table($logModels[$type]);
                if ($date) {
                    $query->where('failed_at', '<=', $date);
                }
                $query->delete();
            } else {
                $query = $logModels[$type]::query();
                if ($date) {
                    $query->where('created_at', '<=', $date);
                }
                $query->delete();
            }
        }
    }

    private function applyListFiltersForLogs(Request $request)
    {
        $this->searchString = $request->input('search-query', '');
        $this->sortOrder = $request->input('sort-order', 'desc');
        $this->sortField = $request->input('sort-field', 'created_at');
        $this->limit = $request->input('limit', 10);
    }
}
