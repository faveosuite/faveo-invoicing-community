<?php

namespace App\Jobs;

use App\BillingLog\Model\MailLog;
use App\Http\Controllers\Common\PhpMailController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(protected mixed $from, protected mixed $to, protected mixed $template_data, protected mixed $template_name, protected mixed $replace = [], protected mixed $type = '', protected mixed $bcc = [], protected mixed $fromname = '', protected mixed $toname = '', protected mixed $cc = [], protected mixed $attach = [], protected mixed $logIdentifier = null, protected mixed $auto_reply = false)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(PhpMailController $phpMailController): void
    {
        if (MailLog::whereId($this->logIdentifier)->value('status') == 'sent') {
            $this->job?->delete();

            return;
        }

        $phpMailController->mailing(
            $this->from,
            $this->to,
            $this->template_data,
            $this->template_name,
            $this->replace,
            $this->type,
            $this->bcc,
            $this->fromname,
            $this->toname,
            $this->cc,
            $this->attach,
            $this->logIdentifier,
            $this->auto_reply
        );
    }
}
