<?php

namespace App\BillingLog\Model;

use App\BaseModel;
use Crypt;

class MailLog extends BaseModel
{
    protected $table = 'mail_logs';

    protected $fillable = [

        /**
         * Foriegn key for log category.
         */
        'log_category_id',

        /**
         * The person to whom we are sending the mail.
         */
        'sender_mail',

        /**
         * The person who receives the mail.
         */
        'receiver_mail',

        /**
         * Subject of the mail.
         */
        'subject',

        /**
         * Body of the mail.
         */
        'body',

        /**
         * Status of the mail `QUEUED`, `SENT`, `ACCEPTED`, `REJECTED`.
         */
        'status',

        /**
         * CCs will be added to it in comma-separated form.
         */
        'carbon_copy',

        /**
         * BCCs will be added to it in comma-separated form.
         */
        'blind_carbon_copy',

        /**
         * Exception log id.
         */
        'exception_log_id',

        'job_payload',
    ];

    protected $appends = ['is_retry'];

    protected $hidden = ['job_payload'];

    protected $htmlAble = ['body'];

    public function exception()
    {
        return $this->belongsTo(ExceptionLog::class, 'exception_log_id');
    }

    public function category()
    {
        return $this->belongsTo(LogCategory::class, 'log_category_id');
    }

    public function getIsRetryAttribute()
    {
        return in_array($this->status, ['failed', 'queued']) && (bool) $this->job_payload;
    }

    public function getJobPayloadAttribute($value)
    {
        return $value ? Crypt::decrypt($value) : null;
    }
}
