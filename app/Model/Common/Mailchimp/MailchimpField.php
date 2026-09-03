<?php

declare(strict_types=1);

namespace App\Model\Common\Mailchimp;

use App\BaseModel;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $list_id
 * @property string $merge_id
 * @property string $name
 * @property string $tag
 * @property string $type
 * @property string $options
 * @property string $required
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField whereListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField whereMergeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField whereTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpField whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MailchimpField extends BaseModel
{
    protected $table = 'mailchimp_fields';

    protected $fillable = ['list_id', 'merge_id', 'name', 'type', 'options', 'required', 'tag'];
}
