<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use Deprecated;

class TaxClass extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'tax_classes';

    protected $fillable = ['name', 'slug'];

    protected $logName = 'tax';

    protected $logNameColumn = 'name';

    protected $logAttributes = [
        'name', 'slug',
    ];

    protected $requireLogUrl = false;

    protected function getMappings(): array
    {
        return [
            'name' => ['Tax Class Name', fn ($value) => $value],
            'slug' => ['Slug', fn ($value) => $value ?: 'standard'],
        ];
    }

    /** Generic tax rates that belong to this class (joined on slug). */
    public function rates()
    {
        return $this->hasMany(TaxRate::class, 'tax_class', 'slug');
    }

    #[Deprecated(message: 'legacy India-GST taxes table; kept for historical data.')]
    public function tax()
    {
        return $this->hasMany(Tax::class, 'tax_classes_id');
    }

    public function tax_product_relation()
    {
        return $this->hasMany(TaxProductRelation::class, 'tax_class_id');
    }
}
