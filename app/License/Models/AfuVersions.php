<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfuVersions extends Model
{
    use HasFactory;

    protected $table = 'product_versions';
    protected $guarded = [];

    protected $primaryKey = 'id';

    public $timestamps = false;

    public function callback()
    {
        return $this->hasMany(AfuCallbacks::class, 'version_id', 'id');
    }
}
