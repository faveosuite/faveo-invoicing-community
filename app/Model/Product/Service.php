<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\BaseModel;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service query()
 * @mixin \Eloquent
 */
class Service extends BaseModel
{
    protected $table = 'services';

    protected $fillable = ['name', 'description'];
}
