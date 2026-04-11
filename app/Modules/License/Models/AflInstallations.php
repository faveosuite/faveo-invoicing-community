<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AflInstallations extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'installation_id';

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(AflProducts::class, 'product_id', 'product_id');
    }

    public function clients()
    {
        return $this->belongsTo(AflClients::class, 'client_id', 'client_id');
    }

    public function license()
    {
        return $this->belongsTo(AflLicenses::class, 'license_code', 'license_code');
    }

    public function getInstallationCountAttribute()
    {
        return AflInstallations::where('product_id', $this->product_id)
            ->when($this->license_code, function ($query) {
                $query->where('license_code', $this->license_code);
            })
            ->when($this->client_id, function ($query) {
                $query->Where('client_id', $this->client_id);
            })
            ->get()->count();
    }

    public function getLatestInstallationDateAttribute()
    {
        return AflInstallations::where('client_id', $this->client_id)
            ->where('product_id', $this->product_id)->latest()->value('installation_date');
    }
}
