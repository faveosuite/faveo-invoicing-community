<?php

declare(strict_types=1);

namespace App\Model\Common\Mailchimp;

use App\BaseModel;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $api_key
 * @property string $list_id
 * @property string $subscribe_status
 * @property string $group_id_products
 * @property string $group_id_is_paid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpSetting whereApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpSetting whereGroupIdIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpSetting whereGroupIdProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpSetting whereListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpSetting whereSubscribeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpSetting whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MailchimpSetting extends BaseModel
{
    protected $table = 'mailchimp_settings';

    protected $fillable = ['api_key', 'list_id', 'subscribe_status'];
}
