<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipedriveFieldOption extends Model
{
    use HasFactory;

    protected $guarded =  [];

    public function pipedriveField()
    {
        return $this->belongsTo(PipedriveField::class, 'pipedrive_field_id');
    }
}
