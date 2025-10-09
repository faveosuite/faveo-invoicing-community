<?php

namespace App\BillingLog\Controllers;

use App\BillingLog\Model\CronLog;
use App\BillingLog\Model\ExceptionLog;
use App\BillingLog\Model\LogCategory;
use App\BillingLog\Model\MailLog;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Laravel\Horizon\Listeners\TrimFailedJobs;

class LogViewController
{
    public function getSystemLogs()
    {
        return view('log::index');
    }

    public function getLogs($type, Request $request)
    {
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

        $date = $request->input('date');
        $logCategoryId = $request->input('category');

        $query = LogCategory::find($logCategoryId)
            ->exceptions()
            ->whereDate('created_at', $date);

        return DataTables::of($query)
            ->editColumn('created_at', function ($row) {
                return new HtmlString(getDateHtml($row->created_at));
            })
            ->make(true);
    }

    public function getCronLogs(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'status' => 'in:completed,failed',
            'category' => 'required',
        ]);

        $date = $request->input('date');
        $status = $request->input('status');
        $category = $request->input('category');

        $query = CronLog::whereDate('created_at', $date)
            ->where('command', $category)
            ->where('status', $status);

        return DataTables::of($query)
            ->editColumn('created_at', function ($row) {
                return new HtmlString(getDateHtml($row->created_at));
            })
            ->make(true);
    }

    public function getMailLogs(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'category' => 'required|exists:log_categories,id',
            'status' => 'in:sent,failed,queued',
        ]);

        $date = $request->input('date');
        $logCategoryId = $request->input('category');
        $status = $request->input('status');

        $query = LogCategory::find($logCategoryId)
            ->mail()
            ->where('status', $status)
            ->whereDate('created_at', $date);

        return DataTables::of($query)
            ->editColumn('created_at', function ($row) {
                return new HtmlString(getDateHtml($row->created_at));
            })
            ->editColumn('updated_at', function ($row) {
                return new HtmlString(getDateHtml($row->updated_at));
            })
            ->make(true);
    }

    public function deleteLogsByDate(array $logTypes, $date = null)
    {
        $logModels = [
            'cron'        => CronLog::class,
            'exception'   => ExceptionLog::class,
            'mail'        => MailLog::class,
            'failed_jobs' => 'failed_jobs', // Use table name for DB query
        ];

        foreach ($logTypes as $type) {
            if (!isset($logModels[$type])) {
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
}
