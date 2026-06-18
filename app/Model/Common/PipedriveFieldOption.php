<?php

declare(strict_types=1);

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $pipedrive_field_id
 * @property string|null $key
 * @property string|null $value
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Model\Common\PipedriveField $pipedriveField
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveFieldOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveFieldOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveFieldOption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveFieldOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveFieldOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveFieldOption whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveFieldOption wherePipedriveFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveFieldOption whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveFieldOption whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveFieldOption whereValue($value)
 * @mixin \Eloquent
 */
class PipedriveFieldOption extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function pipedriveField()
    {
        return $this->belongsTo(PipedriveField::class, 'pipedrive_field_id');
    }
}
