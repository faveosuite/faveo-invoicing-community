<?php

declare(strict_types=1);

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $app_name
 * @property string|null $app_key
 * @property string|null $app_secret
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ThirdPartyApp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ThirdPartyApp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ThirdPartyApp query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ThirdPartyApp whereAppKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ThirdPartyApp whereAppName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ThirdPartyApp whereAppSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ThirdPartyApp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ThirdPartyApp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ThirdPartyApp whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ThirdPartyApp extends Model
{
    use SystemActivityLogsTrait;

    protected $table = 'third_party_apps';

    protected $fillable = ['app_name', 'app_key', 'app_secret'];

    protected string $logName = 'third_party_apps';

    protected string $logNameColumn = 'app_name';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'app_name', 'app_key', 'app_secret',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['third-party-keys'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'app_name' => ['App Name', fn ($value) => $value],
            'app_key' => ['App Key', fn ($value) => $value],
            'app_secret' => ['App Secret', fn ($value) => $value],
        ];
    }
}
