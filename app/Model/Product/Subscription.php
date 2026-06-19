<?php

namespace App\Model\Product;

use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Traits\SystemActivityLogsTrait;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Override;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $user_id
 * @property int $plan_id
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property string|null $update_ends_at
 * @property Carbon|null $ends_at
 * @property string|null $support_ends_at
 * @property int $deny_after_subscription
 * @property string|null $version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $version_updated_at
 * @property int|null $is_subscribed
 * @property string|null $subscribe_id
 * @property string|null $autoRenew_status
 * @property string $rzp_subscription
 * @property int $is_deleted
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Order|null $order
 * @property-read Plan|null $plan
 * @property-read Product|null $product
 * @property-read User|null $user
 *
 * @method static \Database\Factories\Model\Product\SubscriptionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereAutoRenewStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereDenyAfterSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereIsSubscribed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereRzpSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereSubscribeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereSupportEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereUpdateEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereVersionUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Subscription extends Model
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'subscriptions';

    protected $fillable = ['name', 'description', 'days', 'ends_at', 'update_ends_at',
        'user_id', 'plan_id', 'order_id', 'deny_after_subscription', 'version', 'product_id', 'support_ends_at', 'version_updated_at', 'is_subscribed', 'is_deleted', 'autoRenew_status'];

    protected string $logName = 'subscriptions';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'name', 'description', 'days', 'ends_at', 'update_ends_at',
        'user_id', 'plan_id', 'order_id', 'deny_after_subscription', 'version', 'product_id', 'support_ends_at', 'version_updated_at', 'is_subscribed', 'is_deleted',
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'name' => ['Subscription Name', fn ($value) => $value],
            'description' => ['Description', fn ($value) => $value],
            'days' => ['Subscription Days', fn ($value) => $value],
            'ends_at' => ['Subscription End Date', fn ($value) => $value],
            'update_ends_at' => ['Update End Date', fn ($value) => $value],
            'user_id' => ['User', fn ($value) => User::find($value)?->name], // @phpstan-ignore property.notFound
            'plan_id' => ['Plan', fn ($value) => Plan::find($value)?->name], // @phpstan-ignore property.notFound
            'order_id' => ['Order', fn ($value) => $value ? Order::find($value)?->number : 'No Order'], // @phpstan-ignore property.notFound
            'deny_after_subscription' => ['Deny After Subscription', fn ($value): array|string => $value === 1 ? __('message.yes') : __('message.no')],
            'version' => ['Version', fn ($value) => $value],
            'product_id' => ['Product', fn ($value) => Product::find($value)?->name], // @phpstan-ignore property.notFound
            'support_ends_at' => ['Support End Date', fn ($value) => $value],
            'version_updated_at' => ['Version Updated At', fn ($value) => $value],
            'is_subscribed' => ['Is Subscribed', fn ($value): array|string => $value === 1 ? __('message.yes') : __('message.no')],
            'is_deleted' => ['Is Deleted', fn ($value): array|string => $value === 1 ? __('message.yes') : __('message.no')],
        ];
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getLogUrl(mixed $id = null): ?string
    {
        return url('orders/'.$this->order_id);
    }

    // public function getEndsAtAttribute($value)
    // {
    //      $date1 = new DateTime($value);
    //     $tz = \Auth::user()->timezone()->first()->name;
    //     $date1->setTimezone(new DateTimeZone($tz));
    //     $date = $date1->format('M j, Y, g:i a ');

    //     return $date;
    // }

    //    public function delete() {
    //
    //
    //        $this->Plan()->delete();
    //
    //
    //        return parent::delete();
    //    }
    #[Override]
    protected function casts(): array
    {
        return [
            'ends_at' => 'datetime',
        ];
    }
}
