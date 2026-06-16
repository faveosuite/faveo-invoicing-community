<?php

namespace App\Model\License;

use App\Model\Product\Product;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

class LicenseType extends Model
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'license_types';

    protected $fillable = ['id', 'name'];

    protected $logName = 'license_types';

    protected $logNameColumn = 'name';

    protected $logAttributes = [
        'id', 'name',
    ];

    protected $logUrl = [
        'segments' => ['license-type'],
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['License Type', fn ($value) => $value],
        ];
    }

    public function permissions()
    {
        return $this->belongsToMany(LicensePermission::class, 'license_license_permissions')
            ->using(LicensePermissionPivot::class)
            ->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'type');
    }

    #[Override]
    public function delete()
    {
        $this->permissions()->detach();
        $this->products()->delete();

        return parent::delete();
    }
}
