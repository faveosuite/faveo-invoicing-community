<?php

namespace App;

use App\Model\Order\Order;
use Crypt;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Contracts\Encryption\DecryptException;

class WhatsappIntegrationUser extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'whatsapp_integration_user';

    protected $fillable = ['waba_id', 'phone_number_id', 'phone_number', 'user_id', 'access_token', 'user_callback_url', 'business_id', 'order_id'];

    protected static $logName = 'phoneNumber';

    protected $logAttributes = ['waba_id', 'phone_number', 'phone_number_id', 'user_id', 'user_callback_url', 'business_id', 'order_id'];

    protected $logNameColumn = 'phone_number';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function getMappings(): array
    {
        return [
            'user_id' => ['User', fn ($value) => User::find($value)?->user_name],
            'phone_number' => ['Phone Number', fn ($value) => $value],
            'phone_number_id' => ['Phone Number Id', fn ($value) => $value],
            'waba_id' => ['WabaId Id', fn ($value) => $value],
            'order_id' => ['Order Number', fn ($value) => Order::find($value)?->number],
            'user_callback_url' => ['Callback Url', fn ($value) => $value],
            'business_id' => ['Business Id', fn ($value) => $value],
        ];
    }

    protected function setAccessTokenAttribute($value)
    {
        try {
            $this->attributes['access_token'] = Crypt::encrypt($value);
        } catch (DecryptException) {
            // if encryption fails, store original value
            $this->attributes['access_token'] = $value;
        }
    }

    protected function getAccessTokenAttribute($value)
    {
        try {
            $decrypted = Crypt::decrypt($value);

            return $decrypted;
        } catch (DecryptException) {
            return $value;
        }
    }
}
