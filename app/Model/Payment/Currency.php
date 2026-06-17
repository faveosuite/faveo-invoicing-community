<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Common\Country;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Override;

class Currency extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'currencies';

    protected $fillable = ['code', 'symbol', 'name', 'status', 'id', 'dashboard_currency'];

    protected $logName = 'currency';

    protected $logNameColumn = 'name';

    protected $logAttributes = [
        'code', 'symbol', 'name', 'status',
    ];

    protected $logUrl = [
        'segments' => ['currency'],
    ];

    #[Override]
    protected static function booted(): void
    {
        static::addGlobalScope('active_country', function (Builder $builder): void {
            $builder->whereHas('country', function (\Illuminate\Contracts\Database\Query\Builder $query): void {
                $query->where('status', operator: true);
            });
        });
    }

    protected function getMappings(): array
    {
        return [
            'code' => ['Currency Code', fn ($value) => $value],
            'symbol' => ['Currency Symbol', fn ($value) => $value],
            'name' => ['Currency Name', fn ($value) => $value],
            'status' => [$this->name . ' currency status', fn ($value): array|string|null => $value === 1 ? __('message.active') : __('message.inactive')],
        ];
    }

    public function country()
    {
        return $this->hasMany(Country::class, 'currency_id');
    }
}
