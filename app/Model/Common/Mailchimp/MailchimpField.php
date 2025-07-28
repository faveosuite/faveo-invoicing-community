<?php

namespace App\Model\Common\Mailchimp;

use App\BaseModel;

class MailchimpField extends BaseModel
{
    protected $table = 'mailchimp_fields';

    protected $fillable = ['list_id', 'merge_id', 'name', 'type', 'options', 'required', 'tag'];
}
