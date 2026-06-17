<?php

declare(strict_types=1);

namespace App\Model\User;

use Illuminate\Database\Eloquent\Model;

class Password extends Model
{
    protected $table = 'password_resets';

    protected $fillable = ['email', 'token', 'created_at'];

    public $timestamps = false;
}
