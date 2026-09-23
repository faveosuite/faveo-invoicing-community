<?php

namespace App\Model\Order;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property string|null $installation_path
 * @property string|null $installation_ip
 * @property string|null $version
 * @property string|null $last_active
 * @property int $order_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order|null $order
 *
 * @method static \Database\Factories\Model\Order\InstallationDetailFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationDetail whereInstallationIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationDetail whereInstallationPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationDetail whereLastActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationDetail whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationDetail whereVersion($value)
 *
 * @mixin \Eloquent
 */
class InstallationDetail extends Model
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    protected $table = 'installation_details';

    protected $fillable = ['installation_path', 'installation_ip', 'version', 'last_active', 'order_id'];

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    #[Override]
    public function delete()
    {
        $this->order()->delete();

        return parent::delete();
    }
}
