<?php

namespace App\BillingLog\Controllers;

use App\BillingLog\Model\CronLog;
use App\BillingLog\Model\LogCategory;
use App\BillingLog\Model\MailLog;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Throwable;

class LogWriteController
{
    /**
     * Logs the start of a cron job.
     */
    public function cron(string $signature, string $description = ''): ?CronLog
    {
        try {
            return CronLog::create([
                'command' => $signature,
                'description' => $description,
                'status' => 'running',
            ]);
        } catch (Throwable $throwable) {
            $this->exception($throwable, 'cron');

            return null;
        }
    }

    /**
     * Marks a cron job as failed.
     */
    public function cronFailed(int $logId, ?Exception $exception = null): void
    {
        try {
            $cronLog = CronLog::select('id', 'created_at', 'command')->find($logId);

            $exceptionLog = $exception ? $this->exception($exception, 'cron') : null;

            if ($cronLog) {
                $cronLog->update([
                    'status' => 'failed',
                    'exception_log_id' => $exceptionLog?->id,
                    'duration' => (int) Date::now()->diffInSeconds($cronLog->created_at, absolute: true),
                ]);
            }
        } catch (Throwable $throwable) {
            $this->exception($throwable, 'cron');
        }
    }

    /**
     * Marks a cron job as successfully completed.
     */
    public function cronCompleted(int $logId): void
    {
        try {
            $cronLog = CronLog::select('id', 'created_at')->find($logId);

            if ($cronLog) {
                $cronLog->update([
                    'status' => 'completed',
                    'duration' => (int) Date::now()->diffInSeconds($cronLog->created_at, absolute: true),
                ]);
            }
        } catch (Throwable $throwable) {
            $this->exception($throwable, 'cron');
        }
    }

    /**
     * Logs exception along with trace.
     *
     * @param  Throwable  $e  Exception or Error
     * @param  string  $category  Category to which it belongs
     */
    public function exception(Throwable $e, string $category = 'default'): ?\Illuminate\Database\Eloquent\Model
    {
        try {
            $logCategory = LogCategory::firstOrCreate(['name' => $category]);

            return $logCategory->exceptions()->create([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => nl2br($e->getTraceAsString()),
            ]);
        } catch (Throwable) {
            // ignore exception
        }

        return null;
    }

    /**
     * Logs mail send activity.
     * @param array<mixed>|string $cc
     * @param array<mixed>|string $bcc
     */
    public function logMailByCategory(
        string $senderMail,
        string $receiverMail,
        array|string $cc,
        array|string $bcc,
        string $subject,
        string $body,
        ?string $categoryName = null,
    ): ?Model {
        try {
            $category = LogCategory::firstOrCreate(['name' => $categoryName ?? 'default']);

            return $category->mail()->create([
                'sender_mail' => $senderMail,
                'receiver_mail' => $receiverMail,
                'carbon_copy' => in_array($cc, ['', '0', []], strict: true) ? null : $this->formatAddresses($cc),
                'blind_carbon_copy' => in_array($bcc, ['', '0', []], strict: true) ? null : $this->formatAddresses($bcc),
                'subject' => $subject,
                'body' => $body,
                'status' => 'queued',
            ]);
        } catch (Throwable $throwable) {
            $this->exception($throwable, 'mail-send-exception');

            return null;
        }
    }

    /**
     * Format addresses for database storage.
     * @param array<mixed>|string $addresses
     * @return string
     */
    protected function formatAddresses(array|string $addresses): string
    {
        $addresses = is_string($addresses) ? [$addresses] : $addresses;
        return collect($addresses)->map(function ($address) {
            if (is_array($address) && isset($address['address'])) {
                return isset($address['name']) && ! empty($address['name'])
                    ? $address['name'].' <'.$address['address'].'>'
                    : $address['address'];
            }

            return $address;
        })->implode(', ');
    }

    /**
     * Marks outgoing mail as sent.
     */
    public function outgoingMailSent(mixed $logId): void
    {
        MailLog::whereId($logId)->update(['status' => 'sent']);
    }

    /**
     * Marks outgoing mail as failed.
     */
    public function outgoingMailFailed(mixed $logId, Exception $e): void
    {
        $mailLog = MailLog::select('id', 'exception_log_id')->find($logId);

        if ($mailLog instanceof MailLog) {
            if ($mailLog->exception_log_id) {
                // if already exception exists for this, should be deleted so that latest exception can be captured
                $mailLog->exception()->delete();
            }

            $exception = $this->exception($e, 'cron');
            $mailLog->update([
                'status' => 'failed',
                'exception_log_id' => $exception?->id,
            ]);
        }
    }

    public function deleteLogs(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validation
        $validated = $request->validate([
            'to_date' => ['nullable', 'date'],
            'log_types' => ['required', 'array', 'min:1'],
            'log_types.*' => ['in:cron,exception,mail,systemLogs,failed_jobs'],
        ]);

        // Parse to_date with end of day
        $toDate = $validated['to_date'] ? Date::parse($validated['to_date'])->endOfDay() : null;

        new LogViewController()->deleteLogsByDate($validated['log_types'], $toDate);

        return successResponse(__('message.logs_deleted_successfully'));
    }
}
