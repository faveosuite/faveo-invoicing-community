<?php

namespace App;

use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedWhatsappMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedWhatsappMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedWhatsappMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedWhatsappMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedWhatsappMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedWhatsappMessage whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FailedWhatsappMessage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class FailedWhatsappMessage extends Model
{
    protected $table = 'failed_whatsapp_message';

    protected $fillable = ['message'];

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function message(): Attribute
    {
        return Attribute::make(get: function ($value) {
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
