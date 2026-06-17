<?php

declare(strict_types=1);

namespace App\Model\Common\Mailchimp;

use App\BaseModel;

class MailchimpLists extends BaseModel
{
    protected $table = 'mailchimp_lists';

    protected $fillable = ['name', 'list_id'];
}
