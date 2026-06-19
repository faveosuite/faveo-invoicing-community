<?php

namespace App\Model\Github;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use Crypt;

/**
 * @property int $id
 * @property string|null $client_id
 * @property string|null $client_secret
 * @property string|null $username
 * @property string|null $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Github newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Github newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Github query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Github whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Github whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Github whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Github whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Github wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Github whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Github whereUsername($value)
 *
 * @mixin \Eloquent
 */
class Github extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'githubs';

    protected $fillable = ['client_id', 'client_secret', 'username', 'password'];

    protected string $logName = 'github';

    protected string $logNameColumn = 'Settings';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'client_id', 'client_secret', 'username', 'password',
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
            'client_id' => ['Client ID', fn ($value) => $value],
            'client_secret' => ['Client Secret', fn ($value) => $value],
            'username' => ['Username', fn ($value) => $value],
            'password' => ['Password', fn ($value): string => $value ? '********' : ''],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<mixed, mixed>
     */
    protected function password(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value) {
            if ($value) {
                return Crypt::decrypt($value);
            }

            return $value;
        }, set: function ($value): array {
            $value = Crypt::encrypt($value);

            return ['password' => $value];
        });
    }
}
