<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipedriveLocalFields extends Model
{
    use HasFactory;

    protected $table = 'pipedrive_local_fields';

    protected $fillable = [
        'field_name',
        'field_key',
        'field_type',
        'pipedrive_key',
    ];

    public function pipedrive(){
        return $this->belongsTo(PipedriveField::class, 'pipedrive_key');
    }
}
