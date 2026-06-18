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
    public function __construct(protected $from, protected $to, protected $template_data, protected $template_name, protected $replace = [], protected $type = '', protected $bcc = [], protected $fromname = '', protected $toname = '', protected $cc = [], protected $attach = [], protected $logIdentifier = null, protected $auto_reply = false)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PhpMailController $phpMailController)
    {
        if (MailLog::whereId($this->logIdentifier)->value('status') == 'sent') {
            $this->job->delete();

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
