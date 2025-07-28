<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email_log extends Model
{
    use HasFactory;

    protected $table = 'email_log';

    protected $fillable = ['id', 'from', 'to', 'date', 'subject', 'body', 'cc', 'bcc', 'body', 'headers', 'attachments', 'status', 'created_at'];
}
