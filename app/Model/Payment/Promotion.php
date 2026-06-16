<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Product\Product;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Override;

class Promotion extends BaseModel
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'promotions';

    protected $fillable = ['code', 'type', 'uses', 'value', 'start', 'expiry'];

    protected $logName = 'promotions';

    protected $logNameColumn = 'code';

    protected $logAttributes = [
        'code', 'type', 'uses', 'value', 'start', 'expiry',
    ];

    protected $logUrl = [
        'segments' => ['promotions', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'code' => ['Promotion Code', fn ($value) => $value],
            'type' => ['Promotion Type', fn ($value) => $value === 1 ? 'Percentage' : 'Fixed Amount'],
            'uses' => ['Number of Uses', fn ($value) => $value],
            'value' => ['Promotion Value', fn ($value) => $value],
            'start' => ['Start Date', fn ($value) => $value],
            'expiry' => ['Expiry Date', fn ($value) => $value],
        ];
    }

    public function relation()
    {
        return $this->hasMany(PromoProductRelation::class, 'promotion_id');
    }

    #[Override]
    public function delete()
    {
        $this->relation->each(function ($relation): void {
            $relation->delete();
        });

        return parent::delete();
    }

    public function promotionType()
    {
        return $this->belongsTo(PromotionType::class, 'type', 'id');
    }

    public function products()
    {
        return $this->hasOneThrough(
            Product::class,
            PromoProductRelation::class,
            'promotion_id',
            'id',
            'id',
            'product_id'
        );
    }
}
