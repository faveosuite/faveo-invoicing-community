<?php

namespace App\Model\License;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LicenseType extends Model
{
    use LogsActivity;

    protected $table = 'license_types';

    protected $fillable = ['id', 'name'];

    protected static $logName = 'License Type';

    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created') {
            return 'License Type '.$this->name.' was created';
        }
        if ($eventName == 'updated') {
            return 'License Type  <strong> '.$this->name.'</strong> was updated';
        }
        if ($eventName == 'deleted') {
            return 'License Type <strong> '.$this->name.' </strong> was deleted';
        }

        return '';
    }

    public function permissions()
    {
        return $this->belongsToMany(LicensePermission::class, 'license_license_permissions')->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(\App\Model\Product\Product::class, 'type');
    }

    public function delete()
    {
        $this->permissions()->detach();
        $this->products()->delete();

        return parent::delete();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
