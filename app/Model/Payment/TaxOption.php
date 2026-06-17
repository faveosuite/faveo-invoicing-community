<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

/**
 * @property int $id
 * @property int $tax_enable
 * @property int $inclusive
 * @property string $tax_based_on
 * @property int $round_at_subtotal
 * @property int $shop_inclusive
 * @property int $cart_inclusive
 * @property string $Gst_No
 * @property int $rounding
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
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
 * @mixin \Eloquent
 */
class TaxOption extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'tax_rules';

    protected $fillable = ['tax_enable', 'inclusive', 'tax_based_on', 'shop_inclusive', 'cart_inclusive', 'rounding', 'Gst_no', 'cif_no'];

    protected string $logName = 'tax';

    protected string $logNameColumn = 'Settings';

    protected array $logAttributes = [
        'tax_enable', 'inclusive', 'tax_based_on', 'shop_inclusive', 'cart_inclusive', 'rounding', 'Gst_no', 'cif_no',
    ];

    protected bool $requireLogUrl = false;

    protected function getMappings(): array
    {
        return [
            'tax_enable' => ['Tax Enable', fn ($value): array|string|null => $value === 1 ? __('message.active') : __('message.inactive')],
            'inclusive' => ['Prices Entered With Tax', fn ($value): string => $value === 1 ? 'Yes' : 'No'],
            'tax_based_on' => ['Calculate Tax Based On', fn ($value): string => $value === 'base' ? 'Company address' : 'Billing address'],
            'shop_inclusive' => ['Shop Prices Entered With Tax', fn ($value): string => $value === 1 ? 'Yes' : 'No'],
            'cart_inclusive' => ['Cart Prices Entered With Tax', fn ($value): string => $value === 1 ? 'Yes' : 'No'],
            'rounding' => ['Round Tax To Whole Number', fn ($value): array|string|null => $value === 1 ? __('message.enable') : __('message.disable')],
            'Gst_no' => ['GST Number', fn ($value) => $value ?: 'N/A'],
            'cif_no' => ['CIF Number', fn ($value) => $value ?: 'N/A'],
        ];
    }
}
