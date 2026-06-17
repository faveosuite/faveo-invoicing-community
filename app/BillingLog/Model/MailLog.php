<?php

namespace App\BillingLog\Model;

use App\BaseModel;
use Crypt;

/**
 * @property int $id
 * @property int $log_category_id
 * @property string $sender_mail
 * @property string $receiver_mail
 * @property string|null $carbon_copy
 * @property string|null $blind_carbon_copy
 * @property string $subject
 * @property string $body
 * @property string $job_payload
 * @property string|null $status
 * @property int|null $exception_log_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\BillingLog\Model\LogCategory|null $category
 * @property-read \App\BillingLog\Model\ExceptionLog|null $exception
 * @property-read bool $is_retry
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereBlindCarbonCopy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereCarbonCopy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereExceptionLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereJobPayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereLogCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereReceiverMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereSenderMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailLog whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
        'created_at',
    ];

    protected $appends = ['is_retry'];

    protected $hidden = ['job_payload'];

    protected bool $htmlAble = ['body'];

    public function exception()
    {
        return $this->belongsTo(ExceptionLog::class, 'exception_log_id');
    }

    public function category()
    {
        return $this->belongsTo(LogCategory::class, 'log_category_id');
    }

    protected function isRetry(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function (): bool {
            return in_array($this->status, ['failed', 'queued']) && (bool) $this->job_payload;
        });
    }

    protected function jobPayload(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value) {
            return $value ? Crypt::decrypt($value) : null;
        });
    }
}
