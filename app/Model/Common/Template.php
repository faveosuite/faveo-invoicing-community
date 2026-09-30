<?php

declare(strict_types=1);

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property TemplateType|null $type
 * @property string $url
 * @property string $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $reply_to
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereReplyTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereUrl($value)
 *
 * @mixin \Eloquent
 */
class Template extends Model
{
    protected $table = 'templates';

    protected $fillable = ['name', 'data', 'type', 'url', 'reply_to'];

    /**
     * @return HasOne<TemplateType, $this>
     */
    public function type(): HasOne
    {
        return $this->hasOne(TemplateType::class, 'id', 'type');
    }
}
