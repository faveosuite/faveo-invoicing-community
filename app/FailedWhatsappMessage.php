<?php

namespace App;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;

class FailedWhatsappMessage extends Model
{
    protected $table = 'failed_whatsapp_message';
    protected $fillable = ['message'];

    public function setMessageAttribute($value)
    {
        try {
            $this->attributes['message'] = \Crypt::encrypt($value);
        } catch (DecryptException $ex) {
            // if encryption fails, store original value
            $this->attributes['message'] = $value;
        }
    }

    public function getMessageAttribute($value)
    {
        try {
            $decrypted = \Crypt::decrypt($value);

            return $decrypted;
        } catch (DecryptException $ex) {
            return $value;
        }
    }
}
