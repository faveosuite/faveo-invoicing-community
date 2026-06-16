<?php

namespace App;

use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;

class FailedWhatsappMessage extends Model
{
    protected $table = 'failed_whatsapp_message';

    protected $fillable = ['message'];

    protected function setMessageAttribute($value)
    {
        try {
            $this->attributes['message'] = Crypt::encrypt($value);
        } catch (DecryptException) {
            // if encryption fails, store original value
            $this->attributes['message'] = $value;
        }
    }

    protected function getMessageAttribute($value)
    {
        try {
            $decrypted = Crypt::decrypt($value);

            return $decrypted;
        } catch (DecryptException) {
            return $value;
        }
    }
}
