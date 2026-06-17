<?php

namespace App;

use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;

class FailedWhatsappMessage extends Model
{
    protected $table = 'failed_whatsapp_message';

    protected $fillable = ['message'];

    protected function message(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value) {
            try {
                return Crypt::decrypt($value);
            } catch (DecryptException) {
                return $value;
            }
        }, set: function ($value): array {
            try {
                $this->attributes['message'] = Crypt::encrypt($value);
            } catch (DecryptException) {
                // if encryption fails, store original value
                $this->attributes['message'] = $value;
            }

            return ['message' => $value];
        });
    }
}
