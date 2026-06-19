<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $state
 * @property string $c_gst
 * @property string $s_gst
 * @property string $i_gst
 * @property string $ut_gst
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $country
 * @property string $state_code
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState whereCGst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState whereIGst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState whereSGst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState whereStateCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxByState whereUtGst($value)
 *
 * @mixin \Eloquent
 */
class TaxByState extends Model
{
    protected $table = 'tax_by_states';

    protected $fillable = ['country', 'state_code', 'state', 'c_gst', 's_gst', 'i_gst', 'ut_gst'];
}
