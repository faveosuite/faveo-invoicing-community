<?php

namespace App\Model\Product;

use App\License\Models\VersionCallback;
use App\License\Models\VersionInstallation;
use App\Model\Order\Order;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $product_id
 * @property string $title
 * @property string $description
 * @property string $version
 * @property string $file
 * @property array<string, string>|null $build_files
 * @property string|null $version_expire_date
 * @property int $version_install_count
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $is_private
 * @property int $is_restricted
 * @property string|null $dependencies
 * @property int $is_pre_release
 * @property string $release_type
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Collection<int, VersionCallback> $callbacks
 * @property-read int|null $callbacks_count
 * @property-read Collection<int, VersionInstallation> $installations
 * @property-read int|null $installations_count
 * @property-read Order|null $order
 * @property-read Product|null $product
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
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'product_uploads';

    protected $fillable = ['product_id', 'title', 'description', 'version', 'file', 'build_files', 'is_private', 'is_restricted', 'release_type', 'dependencies', 'version_expire_date', 'version_install_count', 'status'];

    /**
     * @var array<string, string>
     */
    protected $casts = ['build_files' => 'array'];

    protected string $logName = 'product';

    protected string $logNameColumn = 'Settings';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'product_id', 'title', 'version', 'file', 'build_files', 'is_private', 'is_restricted', 'release_type',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['edit-upload', ':id'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'product_id' => ['Product', fn ($value) => Product::find($value)?->name], // @phpstan-ignore property.notFound
            'title' => ['Title', fn ($value) => $value],
            'version' => ['Version', fn ($value) => $value],
            'file' => ['File', fn ($value) => $value],
            'build_files' => ['Build Files', fn ($value): string => is_array($value) ? (json_encode($value) ?: '') : (string) $value],
            'is_private' => ['Is Private', fn ($value): array|string => $value === 1 ? __('message.yes') : __('message.no')],
            'is_restricted' => ['Is Restricted', fn ($value): array|string => $value === 1 ? __('message.yes') : __('message.no')],
            'release_type' => ['Release Type', ucfirst(...)],
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Which of this upload's files actually goes out for a download, decided
     * fresh every call from the product's *current* build_type — never fixed
     * at the moment this upload was created. So if a product's build_type is
     * changed after this upload already exists, the very next download picks
     * up the matching file automatically, with no re-upload needed — as long
     * as that build_type's file was saved on this row to begin with.
     *
     * `build_files` is a plain build_type => filename map (e.g.
     * {"obfuscated": "...", "source": "..."}), not hard-coded to exactly two
     * variants — a future third build_type just needs its own key here, no
     * schema change. `file` is the fallback for a product with no build_type
     * set, or no matching entry in the map.
     */
    public function resolvedFile(): ?string
    {
        $buildType = $this->product?->build_type;

        if ($buildType !== null && ! empty($this->build_files[$buildType])) {
            return $this->build_files[$buildType];
        }

        return $this->file;
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return HasMany<VersionCallback, $this>
     */
    public function callbacks(): HasMany
    {
        return $this->hasMany(VersionCallback::class, 'version_id');
    }

    /**
     * @return HasMany<VersionInstallation, $this>
     */
    public function installations(): HasMany
    {
        return $this->hasMany(VersionInstallation::class, 'version_id');
    }

    /**
     * @param  Builder<Model>  $query
     */
    #[Scope]
    protected function active(Builder $query): mixed
    {
        return $query->where(function ($q): void {
            $q->where('status', 1);
        });
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function dependencies(): Attribute
    {
        return Attribute::make(get: function ($value) {
            return json_decode((string) $value);
        });
    }
}
