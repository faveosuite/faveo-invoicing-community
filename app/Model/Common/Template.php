<?php

declare(strict_types=1);

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property \App\Model\Common\TemplateType|null $type
 * @property string $url
 * @property string $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Model\Common\TemplateType, $this>
     */
    public function type(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TemplateType::class, 'id', 'type');
    }
}
