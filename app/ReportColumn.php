<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportColumn extends Model
{
    use HasFactory;
    protected $table = 'report_columns';
    protected $fillable = [
        'key',
        'label',
        'type',
        'default',
    ];

    public function userLinkReports()
    {
        return $this->hasMany(UserLinkReport::class, 'column_id');
    }
}
