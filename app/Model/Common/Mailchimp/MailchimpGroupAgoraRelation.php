<?php

declare(strict_types=1);

namespace App\Model\Common\Mailchimp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $mailchimp_group_cat_id
 * @property string|null $agora_product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroupAgoraRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroupAgoraRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroupAgoraRelation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroupAgoraRelation whereAgoraProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroupAgoraRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroupAgoraRelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroupAgoraRelation whereMailchimpGroupCatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroupAgoraRelation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class MailchimpGroupAgoraRelation extends Model
{
    protected $table = 'mailchimp_group_agora_relations';

    protected $fillable = ['mailchimp_group_cat_id', 'agora_product_id'];
}
