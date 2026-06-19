<?php

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $rzp_key
 * @property string|null $rzp_secret
 * @property string|null $razorpay_webhook_secret
 * @property string|null $apilayer_key
 * @property string|null $bugsnag_api_key
 * @property string|null $zoho_api_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $msg91_auth_key
 * @property string|null $msg91_sender
 * @property string|null $msg91_template_id
 * @property int|null $msg91_third_party_id
 * @property string|null $verification_preference
 * @property string|null $twitter_consumer_key
 * @property string|null $twitter_consumer_secret
 * @property string|null $twitter_access_token
 * @property string|null $access_tooken_secret
 * @property string|null $nocaptcha_sitekey
 * @property string|null $captcha_secretCheck
 * @property string|null $update_api_url
 * @property string|null $update_api_secret
 * @property string|null $terms_url
 * @property string|null $pipedrive_api_key
 * @property int $require_pipedrive_user_verification
 * @property string|null $stripe_key
 * @property string|null $stripe_secret
 * @property string|null $stripe_webhook_secret
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static \Database\Factories\ApiKeyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereAccessTookenSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereApilayerKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereBugsnagApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereCaptchaSecretCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereMsg91AuthKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereMsg91Sender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereMsg91TemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereMsg91ThirdPartyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereNocaptchaSitekey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey wherePipedriveApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereRazorpayWebhookSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereRequirePipedriveUserVerification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereRzpKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereRzpSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereStripeKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereStripeSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereStripeWebhookSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereTermsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereTwitterAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereTwitterConsumerKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereTwitterConsumerSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereUpdateApiSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereUpdateApiUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereVerificationPreference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApiKey whereZohoApiKey($value)
 *
 * @mixin \Eloquent
 */
class ApiKey extends Model
{
    /**
     * @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'api_keys';

    protected $fillable = ['rzp_key', 'rzp_secret', 'apilayer_key', 'bugsnag_api_key',
        'zoho_api_key', 'msg91_auth_key', 'msg91_sender', 'msg91_template_id', 'twitter_consumer_key',
        'twitter_consumer_secret', 'twitter_access_token', 'access_tooken_secret', 'nocaptcha_sitekey', 'captcha_secretCheck', 'update_api_url', 'update_api_secret', 'terms_url', 'pipedrive_api_key', 'stripe_key', 'stripe_secret', 'msg91_third_party_id', 'require_pipedrive_user_verification', 'verification_preference'];

    protected string $logName = 'api_key';

    protected string $logNameColumn = 'Settings';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'rzp_key', 'rzp_secret', 'apilayer_key', 'bugsnag_api_key',
        'zoho_api_key', 'msg91_auth_key', 'msg91_sender', 'msg91_template_id',
        'twitter_consumer_key', 'twitter_consumer_secret', 'twitter_access_token',
        'access_tooken_secret',
        'nocaptcha_sitekey', 'captcha_secretCheck', 'update_api_url',
        'update_api_secret', 'terms_url', 'pipedrive_api_key', 'stripe_key',
        'stripe_secret', 'msg91_third_party_id',
        'require_pipedrive_user_verification', 'verification_preference',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['/third-party-integration'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'rzp_key' => ['Rzp Key', fn ($value) => $value],
            'rzp_secret' => ['Rzp Secret', fn ($value) => $value],
            'apilayer_key' => ['Apilayer Key', fn ($value) => $value],
            'bugsnag_api_key' => ['Bugsnag Api Key', fn ($value) => $value],
            'zoho_api_key' => ['Zoho Api Key', fn ($value) => $value],
            'msg91_auth_key' => ['Msg91 Auth Key', fn ($value) => $value],
            'msg91_sender' => ['Msg91 Sender', fn ($value) => $value],
            'msg91_template_id' => ['Msg91 Template Id', fn ($value) => $value],
            'twitter_consumer_key' => ['Twitter Consumer Key', fn ($value) => $value],
            'twitter_consumer_secret' => ['Twitter Consumer Secret', fn ($value) => $value],
            'twitter_access_token' => ['Twitter Access Token', fn ($value) => $value],
            'access_tooken_secret' => ['Access Token Secret', fn ($value) => $value],
            'nocaptcha_sitekey' => ['Captcha Site key', fn ($value) => $value],
            'captcha_secretCheck' => ['Captcha Secret Key', fn ($value) => $value],
            'update_api_url' => ['Update Api Url', fn ($value) => $value],
            'update_api_secret' => ['Update Api Secret', fn ($value) => $value],
            'terms_url' => ['Terms Url', fn ($value) => $value],
            'pipedrive_api_key' => ['Pipedrive Api Key', fn ($value) => $value],
            'stripe_key' => ['Stripe Key', fn ($value) => $value],
            'stripe_secret' => ['Stripe Secret', fn ($value) => $value],
            'msg91_third_party_id' => ['Msg91 Third Party Id', fn ($value) => $value],
            'require_pipedrive_user_verification' => ['Require Pipedrive User Verification', fn ($value): array|string => $value === 1 ? __('message.yes') : __('message.no')],
            'verification_preference' => ['Verification Preference', fn ($value) => $value],
        ];
    }

    public function getLogUrl(mixed $id = null): ?string
    {
        $fields = ['verification_preference'];

        if ($this->wasChanged($fields)) {
            return url('contact-option');
        }

        return url('third-party-integration');
    }

    public function getLogName(): string
    {
        $fields = ['verification_preference'];

        if ($this->wasChanged($fields)) {
            return 'contact_options';
        }

        return 'api_key';
    }
}
