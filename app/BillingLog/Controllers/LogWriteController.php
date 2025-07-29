<?php

namespace App\BillingLog\Controllers;

use App\BillingLog\Model\CronLog;
use App\BillingLog\Model\LogCategory;
use Carbon\Carbon;
use Exception;
use Throwable;

class LogWriteController
{
    /**
     * Logs the start of a cron job.
     *
     * @param  string  $signature
     * @param  string  $description
     * @return CronLog|null
     */
    public function cron(string $signature, string $description = ''): ?CronLog
    {
        try {
            return CronLog::create([
                'command' => $signature,
                'description' => $description,
                'status' => 'running',
            ]);
        } catch (Throwable $e) {
            $this->exception($e, 'cron');

            return null;
        }
    }

    /**
     * Marks a cron job as failed.
     *
     * @param  int  $logId
     * @param  Exception|null  $exception
     * @return void
     */
    public function cronFailed(int $logId, ?Exception $exception = null): void
    {
        try {
            $cronLog = CronLog::select('id', 'created_at', 'command')->find($logId);

            $exceptionLog = $this->exception($exception, 'cron');

            $cronLog->update([
                'status' => 'failed',
                'exception_log_id' => $exceptionLog?->id,
                'duration' => Carbon::now()->diffInSeconds($cronLog->created_at),
            ]);
        } catch (Throwable $e) {
            $this->exception($e, 'cron');
        }
    }

    /**
     * Marks a cron job as successfully completed.
     *
     * @param  int  $logId
     * @return void
     */
    public function cronCompleted(int $logId): void
    {
        try {
            $cronLog = CronLog::select('id', 'created_at')->find($logId);

            $cronLog->update([
                'status' => 'completed',
                'duration' => Carbon::now()->diffInSeconds($cronLog->created_at),
            ]);
        } catch (Throwable $e) {
            $this->exception($e, 'cron');
        }
    }

    /**
     * Logs exception along with trace.
     *
     * @param  Throwable  $e  Exception or Error
     * @param  string  $category  Category to which it belongs
     * @return void
     */
    public function exception(Throwable $e, string $category = 'default'): ?\Illuminate\Database\Eloquent\Model
    {
        try {
            $logCategory = LogCategory::firstOrCreate(['name' => $category]);

            return $logCategory->exception()->create([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => nl2br($e->getTraceAsString()),
            ]);
        } catch (Throwable $fallback) {
            // ignore exception
        }
    }

    /**
     * Logs mail send activity.
     *
     * @param  string  $senderMail
     * @param  string  $receiverMail
     * @param  array|string  $cc
     * @param  string  $subject
     * @param  string  $body
     * @param  string|int  $refereeId
     * @param  string  $refereeType
     * @param  string|null  $categoryName
     * @param  string  $status
     * @param  string  $source
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function sentMail(
        string $senderMail,
        string $receiverMail,
        array|string $cc,
        string $subject,
        string $body,
        string|int $refereeId,
        string $refereeType,
        ?string $categoryName = null,
        string $status = '',
        string $source = 'default'
    ): ?\Illuminate\Database\Eloquent\Model {
        try {
            $category = LogCategory::firstOrCreate(['name' => $categoryName ?? 'default']);

            return $category->mail()->create([
                'sender_mail' => $senderMail,
                'reciever_mail' => $receiverMail,
                'subject' => $subject,
                'body' => $body,
                'referee_id' => $refereeId,
                'referee_type' => $refereeType,
                'status' => $status,
                'source' => $source,
                'collaborators' => is_array($cc) ? implode(',', $cc) : $cc,
            ]);
        } catch (Throwable $e) {
            $this->exception($e, 'mail-send');

            return null;
        }
    }
}
