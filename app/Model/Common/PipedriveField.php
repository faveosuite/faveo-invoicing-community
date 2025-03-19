<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipedriveField extends Model
{
    use HasFactory;

    protected $table = 'pipedrive_fields';

    protected $fillable = [
        'field_name',
        'field_key',
        'field_type',
        'active',
    ];
}
