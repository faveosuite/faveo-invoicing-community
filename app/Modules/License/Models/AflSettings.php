<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AflSettings extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'SETTING_ID';

    public $timestamps = false;
    public function setEmailPasswordAttribute($value)
{
    $this->attributes['EMAIL_PASSWORD'] = $value ? Crypt::encrypt($value) : $value;
}
public function getEmailPasswordAttribute($value){
    if ($value) {
        try {
            return Crypt::decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return $value;
        }
    }

    return $value;
}
}
