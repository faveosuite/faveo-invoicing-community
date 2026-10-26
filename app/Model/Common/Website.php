<?php

namespace App\Model\Common;

use App\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Website newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Website newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Website query()
 *
 * @mixin \Eloquent
 */
class Website extends BaseModel
{
    /**
     * @return BelongsTo<Model, Model>
     */
    public function customermodel(): BelongsTo
    {
        // Return an Eloquent relationship.
        return $this->belongsTo('User', 'user_id'); // @phpstan-ignore argument.type, argument.templateType, return.type
    }
}
