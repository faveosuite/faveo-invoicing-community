<?php

namespace App\Model\Common\Mailchimp;

use App\BaseModel;

class MailchimpFieldAgoraRelation extends BaseModel
{
    protected $table = 'mailchimp_field_agora_relations';

    protected $fillable = ['first_name', 'last_name', 'company',
        'mobile', 'address', 'town', 'state', 'zip', 'active', 'role', 'source', 'is_paid_yes', 'is_paid_no', ];
}
