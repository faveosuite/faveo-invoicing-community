<?php

namespace App\Model\Product;

use App\License\Models\VersionCallback;
use App\License\Models\VersionInstallation;
use App\Model\Order\Order;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $product_id
 * @property string $title
 * @property string $description
 * @property string $version
 * @property string $file
 * @property string|null $version_expire_date
 * @property int $version_install_count
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_private
 * @property int $is_restricted
 * @property string|null $dependencies
 * @property int $is_pre_release
 * @property string $release_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, VersionCallback> $callbacks
 * @property-read int|null $callbacks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, VersionInstallation> $installations
 * @property-read int|null $installations_count
 * @property-read Order|null $order
 * @property-read \App\Model\Product\Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload active()
 * @method static \Database\Factories\Model\Product\ProductUploadFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereDependencies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereIsPreRelease($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereIsPrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereIsRestricted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereReleaseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereVersionExpireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductUpload whereVersionInstallCount($value)
 *
 * @mixin \Eloquent
 */
class ProductUpload extends Model
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'product_uploads';

    protected $fillable = ['product_id', 'title', 'description', 'version', 'file', 'is_private', 'is_restricted', 'release_type', 'dependencies', 'version_expire_date', 'version_install_count', 'status'];

    protected string $logName = 'product';

    protected string $logNameColumn = 'Settings';

    protected array $logAttributes = [
        'product_id', 'title', 'version', 'file', 'is_private', 'is_restricted', 'release_type',
    ];

    protected array $logUrl = [
        'segments' => ['edit-upload', ':id'],
    ];

    protected function getMappings(): array
    {
        return [
            'product_id' => ['Product', fn ($value) => Product::find($value)?->name],
            'title' => ['Title', fn ($value) => $value],
            'version' => ['Version', fn ($value) => $value],
            'file' => ['File', fn ($value) => $value],
            'is_private' => ['Is Private', fn ($value): array|string => $value === 1 ? __('message.yes') : __('message.no')],
            'is_restricted' => ['Is Restricted', fn ($value): array|string => $value === 1 ? __('message.yes') : __('message.no')],
            'release_type' => ['Release Type', ucfirst(...)],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Product\Product, $this>
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function callbacks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VersionCallback::class, 'version_id');
    }

    public function installations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VersionInstallation::class, 'version_id');
    }

    #[Scope]
    protected function active(\Illuminate\Database\Eloquent\Builder $query): mixed
    {
        return $query->where(function ($q): void {
            $q->where('status', 1);
        });
    }

    protected function dependencies(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value) {
            return json_decode((string) $value);
        });
    }
}
