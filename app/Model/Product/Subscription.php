<?php

namespace App\Model\Product;

use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Traits\SystemActivityLogsTrait;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property int $id
 * @property int $user_id
 * @property int $plan_id
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property string|null $update_ends_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property string|null $support_ends_at
 * @property int $deny_after_subscription
 * @property string|null $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $version_updated_at
 * @property int|null $is_subscribed
 * @property string|null $subscribe_id
 * @property string|null $autoRenew_status
 * @property string $rzp_subscription
 * @property int $is_deleted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Order|null $order
 * @property-read Plan|null $plan
 * @property-read \App\Model\Product\Product|null $product
 * @property-read User|null $user
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
 * @mixin \Eloquent
 */
class Subscription extends Model
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'subscriptions';

    protected $fillable = ['name', 'description', 'days', 'ends_at', 'update_ends_at',
        'user_id', 'plan_id', 'order_id', 'deny_after_subscription', 'version', 'product_id', 'support_ends_at', 'version_updated_at', 'is_subscribed', 'is_deleted', 'autoRenew_status'];

    protected string $logName = 'subscriptions';

    protected array $logAttributes = [
        'name', 'description', 'days', 'ends_at', 'update_ends_at',
        'user_id', 'plan_id', 'order_id', 'deny_after_subscription', 'version', 'product_id', 'support_ends_at', 'version_updated_at', 'is_subscribed', 'is_deleted',
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['Subscription Name', fn ($value) => $value],
            'description' => ['Description', fn ($value) => $value],
            'days' => ['Subscription Days', fn ($value) => $value],
            'ends_at' => ['Subscription End Date', fn ($value) => $value],
            'update_ends_at' => ['Update End Date', fn ($value) => $value],
            'user_id' => ['User', fn ($value) => User::find($value)?->name],
            'plan_id' => ['Plan', fn ($value) => Plan::find($value)?->name],
            'order_id' => ['Order', fn ($value) => $value ? Order::find($value)?->number : 'No Order'],
            'deny_after_subscription' => ['Deny After Subscription', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'version' => ['Version', fn ($value) => $value],
            'product_id' => ['Product', fn ($value) => Product::find($value)?->name],
            'support_ends_at' => ['Support End Date', fn ($value) => $value],
            'version_updated_at' => ['Version Updated At', fn ($value) => $value],
            'is_subscribed' => ['Is Subscribed', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
            'is_deleted' => ['Is Deleted', fn ($value): array|string|null => $value === 1 ? __('message.yes') : __('message.no')],
        ];
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Product\Product, $this>
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Order\Order, $this>
     */
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getLogUrl($id = null): ?string
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
