<?php

namespace App;

use App\Model\Order\Order;
use App\Traits\SystemActivityLogsTrait;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $waba_id
 * @property string $phone_number_id
 * @property string $business_id
 * @property string $phone_number
 * @property string $access_token
 * @property int $user_id
 * @property string $user_callback_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $order_id
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read User|null $user
 *
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
 *
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'user_id' => ['User', fn ($value) => User::find($value)?->user_name], // @phpstan-ignore property.notFound
            'phone_number' => ['Phone Number', fn ($value) => $value],
            'phone_number_id' => ['Phone Number Id', fn ($value) => $value],
            'waba_id' => ['WabaId Id', fn ($value) => $value],
            'order_id' => ['Order Number', fn ($value) => Order::find($value)?->number], // @phpstan-ignore property.notFound
            'user_callback_url' => ['Callback Url', fn ($value) => $value],
            'business_id' => ['Business Id', fn ($value) => $value],
        ];
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function accessToken(): Attribute
    {
        return Attribute::make(get: function ($value) {
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
