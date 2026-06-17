<?php

namespace App\Model\License;

use App\Model\Product\Product;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \App\Model\License\LicensePermissionPivot|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\License\LicensePermission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $products
 * @property-read int|null $products_count
 * @method static \Database\Factories\Model\License\LicenseTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LicenseType extends Model
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'license_types';

    protected $fillable = ['id', 'name'];

    protected string $logName = 'license_types';

    protected string $logNameColumn = 'name';

    protected array $logAttributes = [
        'id', 'name',
    ];

    protected array $logUrl = [
        'segments' => ['license-type'],
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['License Type', fn ($value) => $value],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Model\License\LicensePermission, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(LicensePermission::class, 'license_license_permissions')
            ->using(LicensePermissionPivot::class)
            ->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'type');
    }

    #[Override]
    public function delete()
    {
        $this->permissions()->detach();
        $this->products()->delete();

        return parent::delete();
    }
}
