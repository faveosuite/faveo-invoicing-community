<?php

namespace App\Model\Common\Mailchimp;

use Illuminate\Database\Eloquent\Model;

class MailchimpGroupAgoraRelation extends Model
{
    protected $table = 'mailchimp_group_agora_relations';

    protected $fillable = ['mailchimp_group_cat_id', 'agora_product_id'];
}
