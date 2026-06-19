<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key_id
 * @property string $key_secret
 * @property string $redirect_url
 * @property string $cancel_url
 * @property string $ccavanue_url
 * @property string $currencies
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Razorpay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Razorpay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Razorpay query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Razorpay whereCancelUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Razorpay whereCcavanueUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Razorpay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Razorpay whereCurrencies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Razorpay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Razorpay whereKeyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Razorpay whereKeySecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Razorpay whereRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Razorpay whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Razorpay extends Model
{
    //
}
