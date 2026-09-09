<?php

declare(strict_types=1);

namespace App\Plugins\Razorpay\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $image_url
 * @property string $processing_fee
 * @property string $base_currency
 * @property string $currencies
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RazorpayPayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RazorpayPayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RazorpayPayment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RazorpayPayment whereBaseCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RazorpayPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RazorpayPayment whereCurrencies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RazorpayPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RazorpayPayment whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RazorpayPayment whereProcessingFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RazorpayPayment whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class RazorpayPayment extends Model
{
    protected $table = 'razorpay';

    protected $fillable = ['image_url', 'processing_fee', 'base_currency', 'supported_currencies'];
}
