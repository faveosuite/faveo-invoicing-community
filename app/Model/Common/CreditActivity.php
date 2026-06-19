<?php

declare(strict_types=1);

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $payment_id
 * @property string $text
 * @property string $role
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditActivity wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditActivity whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditActivity whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditActivity whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CreditActivity extends Model
{
    protected $table = 'credit_activity';

    protected $fillable = ['payment_id', 'text', 'role', 'created_at', 'updated_at'];
}
