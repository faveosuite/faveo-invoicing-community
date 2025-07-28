<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLinkReport extends Model
{
    use HasFactory;
    protected $table = 'users_link_reports';
    protected $fillable = [
        'user_id',
        'column_id',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reportColumn()
    {
        return $this->belongsTo(ReportColumn::class, 'column_id');
    }
}
