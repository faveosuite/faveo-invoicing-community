<?php

namespace App;

use App\Model\Order\Order;
use App\Traits\SystemActivityLogsTrait;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * @property int $id
 * @property string $waba_id
 * @property string $phone_number_id
 * @property string $business_id
 * @property string $phone_number
 * @property string $access_token
 * @property int $user_id
 * @property string $user_callback_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $order_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser wherePhoneNumberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser whereUserCallbackUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegrationUser whereWabaId($value)
 * @mixin \Eloquent
 */
class WhatsappIntegrationUser extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'whatsapp_integration_user';

    protected $fillable = ['waba_id', 'phone_number_id', 'phone_number', 'user_id', 'access_token', 'user_callback_url', 'business_id', 'order_id'];

    protected static string $logName = 'phoneNumber';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = ['waba_id', 'phone_number', 'phone_number_id', 'user_id', 'user_callback_url', 'business_id', 'order_id'];

    protected string $logNameColumn = 'phone_number';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<mixed>
     */
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

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<mixed, mixed>
     */
    protected function accessToken(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value) {
            try {
                return Crypt::decrypt($value);
            } catch (DecryptException) {
                return $value;
            }
        }, set: function ($value): array {
            try {
                $this->attributes['access_token'] = Crypt::encrypt($value);
            } catch (DecryptException) {
                // if encryption fails, store original value
                $this->attributes['access_token'] = $value;
            }

            return ['access_token' => $value];
        });
    }
}
