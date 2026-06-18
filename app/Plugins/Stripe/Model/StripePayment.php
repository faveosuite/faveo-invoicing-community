<?php

declare(strict_types=1);

namespace App\Plugins\Stripe\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $image_url
 * @property string $processing_fee
 * @property string $base_currency
 * @property string $currencies
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripePayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripePayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripePayment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripePayment whereBaseCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripePayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripePayment whereCurrencies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripePayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripePayment whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripePayment whereProcessingFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripePayment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StripePayment extends Model
{
    protected $table = 'stripe';

    protected $fillable = ['image_url', 'processing_fee', 'base_currency', 'supported_currencies'];
}
