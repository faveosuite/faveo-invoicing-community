<?php

namespace App\Model\Common\Mailchimp;

use App\BaseModel;

class MailchimpSetting extends BaseModel
{
    protected $table = 'mailchimp_settings';

    protected $fillable = ['api_key', 'list_id', 'subscribe_status'];
}
