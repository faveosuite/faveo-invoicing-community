<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $tax_enable
 * @property int $inclusive
 * @property string $tax_based_on
 * @property int $round_at_subtotal
 * @property int $shop_inclusive
 * @property int $cart_inclusive
 * @property int $rounding
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $Gst_No
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption whereCartInclusive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption whereGstNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption whereInclusive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption whereRoundAtSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption whereRounding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption whereShopInclusive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption whereTaxBasedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption whereTaxEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxOption whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TaxOption extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'tax_rules';

    protected $fillable = ['tax_enable', 'inclusive', 'tax_based_on', 'shop_inclusive', 'cart_inclusive', 'rounding', 'Gst_no', 'cif_no'];

    protected string $logName = 'tax';

    protected string $logNameColumn = 'Settings';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'tax_enable', 'inclusive', 'tax_based_on', 'shop_inclusive', 'cart_inclusive', 'rounding', 'Gst_no', 'cif_no',
    ];

    protected function requiresLogUrl(): bool
    {
        return false;
    }

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        $yesNo = self::label('Yes', 'No');
        $orNa = fn ($value) => $value ?: 'N/A';

        return [
            'tax_enable' => ['Tax Enable', self::label(__('message.active'), __('message.inactive'))],
            'inclusive' => ['Prices Entered With Tax', $yesNo],
            'tax_based_on' => ['Calculate Tax Based On', self::label('Company address', 'Billing address', 'base')],
            'shop_inclusive' => ['Shop Prices Entered With Tax', $yesNo],
            'cart_inclusive' => ['Cart Prices Entered With Tax', $yesNo],
            'rounding' => ['Round Tax To Whole Number', self::label(__('message.enable'), __('message.disable'))],
            'Gst_no' => ['GST Number', $orNa],
            'cif_no' => ['CIF Number', $orNa],
        ];
    }

    /**
     * Formatter showing $on when the value is exactly $match, else $off.
     *
     * @param  array<mixed>|string  $on
     * @param  array<mixed>|string  $off
     */
    private static function label(array|string $on, array|string $off, mixed $match = 1): \Closure
    {
        return fn ($value): array|string => $value === $match ? $on : $off;
    }
}
