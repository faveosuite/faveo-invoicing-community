<?php

namespace App\Model\Common\Mailchimp;

use Illuminate\Database\Eloquent\Model;

class MailchimpGroup extends Model
{
    protected $table = 'mailchimp_groups';

    protected $fillable = ['category_id', 'list_id', 'category_option_id', 'category_name'];
}
