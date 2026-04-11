<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfuCallbacks extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'callback_id';

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(AfuProducts::class,'product_id','product_id');
    }
    public function version()
    {
        return $this->belongsTo(AfuVersions::class,'version_id','version_id');
    }
    public function types()
    {
        return $this->belongsTo(CallbackTypes::class,'callback_type','key');
    }
}
