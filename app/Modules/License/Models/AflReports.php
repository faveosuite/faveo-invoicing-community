<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AflReports extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'report_id';
    public function user() {
        return $this->belongsTo(AflClients::class, 'account_id', 'client_id');
    }
    public function product() {
        return $this->belongsTo(AflProducts::class, 'product_id');
    }
    public function license()
    {
        return $this->belongsTo(AflLicenses::class, 'license_code', 'license_code');
    }
    public function scopeWithUserFormatted($query)
    {
        $query->addSelect([
            'user_formatted' => DB::raw("
            CASE
                WHEN users.client_email LIKE '%_@__%.__%' THEN
                    CASE
                        WHEN users.client_fname IS NOT NULL AND users.client_lname IS NOT NULL THEN CONCAT(users.client_fname, ' ', users.client_lname)
                        ELSE users.client_email
                    END
                ELSE 'System'
            END AS user_formatted
        ")
        ])
            ->leftJoin('users', 'afl_reports.account_id', '=', 'users.client_id')
            ->limit(1);
    }
}
