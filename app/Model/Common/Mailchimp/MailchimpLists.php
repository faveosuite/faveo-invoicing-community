<?php

declare(strict_types=1);

namespace App\Model\Common\Mailchimp;

use App\BaseModel;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $list_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpLists newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpLists newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpLists query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpLists whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpLists whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpLists whereListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpLists whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpLists whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MailchimpLists extends BaseModel
{
    protected $table = 'mailchimp_lists';

    protected $fillable = ['name', 'list_id'];
}
