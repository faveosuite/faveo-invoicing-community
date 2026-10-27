<?php

declare(strict_types=1);

namespace App\Model\Common\Mailchimp;

use App\BaseModel;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $company
 * @property string|null $mobile
 * @property string|null $address
 * @property string|null $country
 * @property string|null $town
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $active
 * @property string|null $role
 * @property string|null $source
 * @property string|null $is_paid_yes
 * @property string|null $is_paid_no
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereIsPaidNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereIsPaidYes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereTown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpFieldAgoraRelation whereZip($value)
 *
 * @mixin \Eloquent
 */
class MailchimpFieldAgoraRelation extends BaseModel
{
    protected $table = 'mailchimp_field_agora_relations';

    protected $fillable = ['first_name', 'last_name', 'company',
        'mobile', 'address', 'town', 'country', 'state', 'zip', 'active', 'role', 'source', 'is_paid_yes', 'is_paid_no', ];
}
