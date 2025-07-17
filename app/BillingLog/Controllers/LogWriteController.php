<?php

namespace App\BillingLog\Controllers;

use App\BillingLog\Model\CronLog;
use App\BillingLog\Model\LogCategory;
use App\BillingLog\Model\MailLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
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
    public function exception(Throwable $e, string $category = 'default')
    {
        try {
            $logCategory = LogCategory::firstOrCreate(['name' => $category]);

            return $logCategory->exceptions()->create([
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
    public function logMailByCategory(
        string $senderMail,
        string $receiverMail,
        array|string $cc,
        array|string $bcc,
        string $subject,
        string $body,
        ?string $categoryName = null,
    ): ?\Illuminate\Database\Eloquent\Model {
        try {
            $category = LogCategory::firstOrCreate(['name' => $categoryName ?? 'default']);

            return $category->mail()->create([
                'sender_mail' => $senderMail,
                'receiver_mail' => $receiverMail,
                'carbon_copy' => ! empty($cc) ? $this->formatAddresses($cc) : null,
                'blind_carbon_copy' => ! empty($bcc) ? $this->formatAddresses($bcc) : null,
                'subject' => $subject,
                'body' => $body,
                'status' => 'queued',
            ]);
        } catch (Throwable $e) {
            $this->exception($e, 'mail-send-exception');

            return null;
        }
    }

    /**
     * Format addresses for database storage.
     */
    protected function formatAddresses(array $addresses): string
    {
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
    public function outgoingMailSent($logId)
    {
        MailLog::whereId($logId)->update(['status' => 'sent']);
    }

    /**
     * Marks outgoing mail as failed.
     */
    public function outgoingMailFailed($logId, Exception $e)
    {
        $mailLog = MailLog::select('id', 'exception_log_id')->find($logId);

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

    public function deleteLogs(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'to_date' => 'nullable|date',
            'log_types' => 'required|array|min:1',
            'log_types.*' => 'in:cron,exception,mail',
        ]);

        // Parse to_date with end of day
        $toDate = $validated['to_date'] ? Carbon::parse($validated['to_date'])->endOfDay() : null;

        (new LogViewController())->deleteLogsByDate($validated['log_types'], $toDate);

        return successResponse('Logs deleted successfully');
    }
}
