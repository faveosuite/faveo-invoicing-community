<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string $provider
 * @property string|null $type
 * @property string|null $api_key
 * @property string|null $api_secret
 * @property string|null $mode
 * @property string|null $accepted_output
 * @property int $to_use
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders whereAcceptedOutput($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders whereApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders whereApiSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders whereToUse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailMobileValidationProviders whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EmailMobileValidationProviders extends Model
{
    use SystemActivityLogsTrait;

    protected $table = 'email_mobile_validation_providers';

    protected $fillable = ['provider', 'api_key', 'api_secret', 'mode', 'accepted_output'];

    protected string $logName = 'validation-provider';

    protected string $logNameColumn = 'Third Party Keys';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'provider,api_key', 'api_secret', 'mode', 'accepted_output',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['third-party-integration'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'provider' => ['Provider', fn ($value) => $value],
            'api_key' => ['API Key', fn ($value) => $value],
            'api_secret' => ['API Secret', fn ($value) => $value],
            'mode' => ['Mode', fn ($value) => $value],
            'accepted_output' => ['Accepted Output', fn ($value) => $value],
        ];
    }
}
