<?php

namespace App\Model\License;

use App\Model\Product\Product;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Override;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string|null $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read LicensePermissionPivot|null $pivot
 * @property-read Collection<int, LicensePermission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 *
 * @method static \Database\Factories\Model\License\LicenseTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class LicenseType extends Model
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'license_types';

    protected $fillable = ['id', 'name'];

    protected string $logName = 'license_types';

    protected string $logNameColumn = 'name';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'id', 'name',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['license-type'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'name' => ['License Type', fn ($value) => $value],
        ];
    }

    /**
     * @return BelongsToMany<LicensePermission, $this, LicensePermissionPivot>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(LicensePermission::class, 'license_license_permissions')
            ->using(LicensePermissionPivot::class)
            ->withTimestamps();
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
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
