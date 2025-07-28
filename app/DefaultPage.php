<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DefaultPage extends Model
{
    protected $table = 'default_pages';

    protected $fillable = ['page_id', 'page_url'];
}
