<?php

namespace App\BillingLog\Controllers;

use App\BillingLog\Model\CronLog;
use App\BillingLog\Model\ExceptionLog;
use App\BillingLog\Model\LogCategory;
use App\BillingLog\Model\MailLog;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use DB;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Queue\Jobs\Job;
use Lang;
use Logger;

class AutomationController extends Job implements \Illuminate\Contracts\Queue\Job
{
    public ?string $rawBody = null;

    public function getAutomationLog(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'log_type' => ['required', 'in:exception,cron,mail'],
        ]);

        $date = (string) $request->date;
        $type = strtolower($request->log_type);

        switch ($type) {
            case 'cron':
                return successResponse('', $this->getCronCommands($date));

            case 'mail':
                return successResponse('', $this->getMailCategoryLog($date));

            case 'exception':
                return successResponse('', $this->getExceptionCategoryLog($date));
            default:
                return errorResponse('Invalid log type', 400);
        }
    }

    private function getCronCommands(string $date): mixed
    {
        return CronLog::select('command', 'status', DB::raw('count(id) as status_count'))
            ->whereBetween('created_at', [userDayStart($date), userDayEnd($date)])
            ->groupBy('command', 'status')
            ->cursor()
            ->groupBy('command')
            ->map(fn ($logs, $command): array => array_merge([
                'command' => $command,
                'name' => $this->translatedOr((string) $command),
            ], $logs->pluck('status_count', 'status')->toArray()))->values();
    }

    private function getMailCategoryLog(string $date): mixed
    {
        $categoryNames = LogCategory::pluck('name', 'id');

        return MailLog::select('status', 'log_category_id', DB::raw('count(id) as status_count'))
            ->whereBetween('created_at', [userDayStart($date), userDayEnd($date)])
            ->groupBy('log_category_id', 'status')
            ->cursor()
            ->groupBy('log_category_id')
            ->map(fn ($logs, $categoryId): array => array_merge([
                'id' => $categoryId,
                'name' => $this->categoryLabel((string) ($categoryNames[$categoryId] ?? ''), withTemplate: true),
            ], $logs->pluck('status_count', 'status')->toArray()))->values();
    }

    /**
     * Display name of a log category: its mail template's name (mail logs only), else the
     * translated category, else the raw key. An unknown category shows as ''.
     */
    private function categoryLabel(string $key, bool $withTemplate = false): mixed
    {
        if (! $key) {
            return '';
        }

        if ($withTemplate) {
            $templateName = Template::where('type', TemplateType::where('name', $key)->value('id'))->value('name');

            if ($templateName) {
                return $templateName;
            }
        }

        return $this->translatedOr($key);
    }

    /** The log module's translation of $key, else $key itself. */
    private function translatedOr(string $key): mixed
    {
        return Lang::has('log::lang.'.$key) ? __('log::lang.'.$key) : $key;
    }

    private function getExceptionCategoryLog(string $date): mixed
    {
        $categoryNames = LogCategory::pluck('name', 'id');

        return ExceptionLog::select('log_category_id', DB::raw('count(id) as count'))
            ->whereBetween('created_at', [userDayStart($date), userDayEnd($date)])
            ->groupBy('log_category_id')
            ->get()
            ->map(fn ($log): array => [ // @phpstan-ignore method.unresolvableReturnType, argument.unresolvableType
                'id' => $log->log_category_id,
                'name' => $this->categoryLabel((string) ($categoryNames[$log->log_category_id] ?? '')),
                'count' => $log->count, // @phpstan-ignore property.notFound
            ]);
    }

    public function dispatchPayload(mixed $id): JsonResponse
    {
        try {
            /** @var MailLog $mailLog */
            $mailLog = MailLog::findOrFail($id);

            $this->rawBody = $mailLog->job_payload;

            $this->container = Container::getInstance();

            $this->fire();

            Logger::outgoingMailSent($id);

            return successResponse(trans('log::lang.queued_dispatch_successfully'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return errorResponse(__('message.record_not_found'));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    public function getJobId(): int|string|null
    {
        return null;
    }

    public function getRawBody(): string
    {
        return $this->rawBody ?? '';
    }

    public function attempts()
    {
        return 5;
    }
}
