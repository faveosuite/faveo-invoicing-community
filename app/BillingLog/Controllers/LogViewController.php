<?php

namespace App\BillingLog\Controllers;

use App\BillingLog\Model\CronLog;
use App\BillingLog\Model\ExceptionLog;
use App\BillingLog\Model\LogCategory;
use App\BillingLog\Model\MailLog;
use DB;
use Exception;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogViewController
{
    private string $searchString = '';

    private string $sortOrder = 'desc';

    private string $sortField = 'created_at';

    private int $limit = 10;

    public function getSystemLogs(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('log::index');
    }

    public function getLogs(mixed $type, Request $request): mixed
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

        return errorResponse(__('message.invalid_log_type'), 400);
    }

    public function getExceptionLogs(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', 'exists:log_categories,id'],
        ]);

        try {
            $date = $request->input('date');
            $logCategoryId = $request->input('category');

            /** @var \App\BillingLog\Model\LogCategory|null $exceptionCategory */
            $exceptionCategory = LogCategory::find($logCategoryId);

            if (! $exceptionCategory) {
                return errorResponse(__('message.record_not_found'), 404);
            }

            $exceptionLog = $exceptionCategory->exceptions()
                ->whereDate('created_at', $date)
                ->when($this->searchString, function ($q): void {
                    $search = $this->searchString;
                    $q->where(function ($q) use ($search): void {
                        $q->where('message', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('file', 'like', sprintf('%%%s%%', $search));
                    });
                })
                ->orderBy($this->sortField, $this->sortOrder)
                ->simplePaginate($this->limit);

            return successResponse(__('message.exceptions_fetched_successfully'), $exceptionLog);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function getCronLogs(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'status' => ['in:completed,failed'],
            'category' => ['required'],
        ]);

        try {
            $date = $request->input('date');
            $status = $request->input('status');
            $cronCategory = $request->input('category');

            $cronLogs = CronLog::whereDate('created_at', $date)
                ->where('command', $cronCategory)
                ->when($status, fn ($q) => $q->where('status', $status))
                ->when($this->searchString, function ($q): void {
                    $search = $this->searchString;
                    $q->where(function ($q) use ($search): void {
                        $q->where('description', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('command', 'like', sprintf('%%%s%%', $search));
                    });
                })
                ->orderBy($this->sortField, $this->sortOrder)
                ->simplePaginate($this->limit);

            return successResponse(__('message.crons_fetched_successfully'), $cronLogs);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function getMailLogs(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', 'exists:log_categories,id'],
            'status' => ['in:sent,failed,queued'],
        ]);

        try {
            $date = $request->input('date');
            $logCategoryId = $request->input('category');
            $status = $request->input('status');
            /** @var \App\BillingLog\Model\LogCategory|null $mailCategory */
            $mailCategory = LogCategory::find($logCategoryId);

            if (! $mailCategory) {
                return errorResponse(__('message.record_not_found'), 404);
            }

            $mailLogs = $mailCategory->mail()
                ->whereDate('created_at', $date)
                ->when($status, fn ($q) => $q->where('status', $status))
                ->when($this->searchString, function ($q): void {
                    $search = $this->searchString;
                    $q->where(function ($sub) use ($search): void {
                        $sub->where('sender_mail', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('receiver_mail', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('carbon_copy', 'like', sprintf('%%%s%%', $search));
                    });
                })
                ->orderBy($this->sortField, $this->sortOrder)
                ->simplePaginate($this->limit);

            return successResponse(__('message.mail_logs_fetched_successfully'), $mailLogs);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    /**
     * @param  array<mixed>  $logTypes
     */
    public function deleteLogsByDate(array $logTypes, mixed $date = null): void
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
                $query = DB::table($logModels[$type]);
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

    private function applyListFiltersForLogs(Request $request): void
    {
        $this->searchString = $request->input('search-query', '');
        $this->sortOrder = $request->input('sort-order', 'desc');
        $this->sortField = $request->input('sort-field', 'created_at');
        $this->limit = $request->input('limit', 10);
    }
}
