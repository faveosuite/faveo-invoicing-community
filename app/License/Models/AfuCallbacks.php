<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfuCallbacks extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'callback_id';

    public $timestamps = false;

    public function version()
    {
        return $this->belongsTo(AfuVersions::class, 'version_id', 'version_id');
    }
}
