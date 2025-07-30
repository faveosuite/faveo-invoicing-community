<?php

namespace App\Jobs;

use App\Http\Controllers\Common\PhpMailController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    protected $from;

    protected $to;

    protected $template_data;

    protected $template_name;

    protected $replace;

    protected $type;

    protected $bcc;
    protected $fromname;
    protected $toname;

    protected $cc;

    protected $attach;

    protected $logIdentifier;

    protected $auto_reply;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $from,
        $to,
        $template_data,
        $template_name,
        $replace = [],
        $type = '',
        $bcc = [],
        $fromname = '',
        $toname = '',
        $cc = [],
        $attach = [],
        $logIdentifier,
        $auto_reply = false,
    ) {
        $this->from = $from;
        $this->to = $to;
        $this->template_data = $template_data;
        $this->template_name = $template_name;
        $this->replace = $replace;
        $this->type = $type;
        $this->bcc = $bcc;
        $this->fromname = $fromname;
        $this->toname = $toname;
        $this->cc = $cc;
        $this->attach = $attach;
        $this->logIdentifier = $logIdentifier;
        $this->auto_reply = $auto_reply;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PhpMailController $phpMailController)
    {
        $p = $phpMailController->mailing(
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

        return $p;
    }
}
