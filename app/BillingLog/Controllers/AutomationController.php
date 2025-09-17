<?php

namespace App\BillingLog\Controllers;

use App\BillingLog\Model\CronLog;
use App\BillingLog\Model\ExceptionLog;
use App\BillingLog\Model\LogCategory;
use App\BillingLog\Model\MailLog;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Queue\Jobs\Job;
use Logger;

class AutomationController extends Job implements \Illuminate\Contracts\Queue\Job
{
    public $rawBody;

    public function getAutomationLog(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'log_type' => 'required|in:exception,cron,mail',
        ]);

        $date = $this->parseDate($request->date);
        $type = strtolower($request->log_type);

        switch ($type) {
            case 'cron':
                return successResponse('', $this->getCronCommands($date));

            case 'mail':
                return successResponse('', $this->getMailCategoryLog($date));

            case 'exception':
                return successResponse('', $this->getExceptionCategoryLog($date));
        }
    }

    private function parseDate($date)
    {
        return Carbon::parse($date ?? Carbon::today());
    }

    private function getCronCommands(Carbon $date)
    {
        return CronLog::select('command', 'status', \DB::raw('count(id) as status_count'))
            ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->endOfDay()])
            ->groupBy('command', 'status')
            ->cursor()
            ->groupBy('command')
            ->map(function ($logs, $command) {
                return array_merge([
                    'command' => $command,
                    'name' => \Lang::has('log::lang.'.$command)
                        ? __('log::lang.'.$command)
                        : $command,
                ], $logs->pluck('status_count', 'status')->toArray());
            })->values();
    }

    private function getMailCategoryLog(Carbon $date)
    {
        $categoryNames = LogCategory::pluck('name', 'id');

        return MailLog::select('status', 'log_category_id', \DB::raw('count(id) as status_count'))
            ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->endOfDay()])
            ->groupBy('log_category_id', 'status')
            ->cursor()
            ->groupBy('log_category_id')
            ->map(function ($logs, $categoryId) use ($categoryNames) {
                return array_merge([
                    'id' => $categoryId,
                    'name' => ($key = $categoryNames[$categoryId] ?? '')
                        ? (Template::where('type', TemplateType::where('name', $key)->value('id'))->value('name')
                            ?: (\Lang::has("log::lang.$key") ? __("log::lang.$key") : $key))
                        : '',
                ], $logs->pluck('status_count', 'status')->toArray());
            })->values();
    }

    private function getExceptionCategoryLog(Carbon $date)
    {
        $categoryNames = LogCategory::pluck('name', 'id');

        return ExceptionLog::select('log_category_id', \DB::raw('count(id) as count'))
            ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->endOfDay()])
            ->groupBy('log_category_id')
            ->get()
            ->map(function ($log) use ($categoryNames) {
                return [
                    'id' => $log->log_category_id,
                    'name' => ($key = $categoryNames[$log->log_category_id] ?? '')
                        ? (\Lang::has("log::lang.$key") ? __("log::lang.$key") : $key)
                        : '',
                    'count' => $log->count,
                ];
            });
    }

    public function dispatchPayload($id)
    {
        try {
            $mailLog = MailLog::findOrFail($id);

            $this->rawBody = $mailLog->job_payload;

            $this->container = Container::getInstance();

            $this->fire();

            Logger::outgoingMailSent($id);

            return successResponse(trans('log::lang.queued_dispatch_successfully'));
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getJobId()
    {
        return null;
    }

    public function getRawBody()
    {
        return $this->rawBody;
    }

    public function attempts()
    {
        return 5;
    }
}
