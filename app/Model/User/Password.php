<?php

declare(strict_types=1);

namespace App\Model\User;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $email
 * @property string $token
 * @property string $created_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Password newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Password newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Password query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Password whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Password whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Password whereToken($value)
 * @mixin \Eloquent
 */
class Password extends Model
{
    protected $table = 'password_resets';

    protected $fillable = ['email', 'token', 'created_at'];

    public $timestamps = false;
}
