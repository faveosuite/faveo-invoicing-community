<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

class Promotion extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'promotions';

    protected $fillable = ['code', 'type', 'uses', 'value', 'start', 'expiry'];

    protected $logName = 'promotions';

    protected $logNameColumn = 'Settings';

    protected $logAttributes = [
        'code', 'type', 'uses', 'value', 'start', 'expiry',
    ];

    protected $logUrl = ['promotions', 'edit'];

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
        return $this->hasMany(\App\Model\Payment\PromoProductRelation::class, 'promotion_id');
    }

    public function delete()
    {
        $this->relation()->delete();

        return parent::delete();
    }
}
