<?php

namespace App\Model\Common;

use App\BaseModel;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Website newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Website newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Website query()
 * @mixin \Eloquent
 */
class Website extends BaseModel
{
    // use SubscriptionBillableTrait;

    public function customermodel()
    {
        // Return an Eloquent relationship.
        return $this->belongsTo('User', 'user_id');
    }
}
