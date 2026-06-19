<?php

declare(strict_types=1);

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $field_name
 * @property string|null $field_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Model\Common\PipedriveField|null $pipedrive
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveLocalFields newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveLocalFields newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveLocalFields query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveLocalFields whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveLocalFields whereFieldKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveLocalFields whereFieldName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveLocalFields whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveLocalFields whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PipedriveLocalFields extends Model
{
    /**
     * @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    use HasFactory;

    protected $table = 'pipedrive_local_fields';

    protected $fillable = [
        'field_name',
        'field_key',
        'field_type',
        'pipedrive_key',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<PipedriveField, $this>
     */
    public function pipedrive(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PipedriveField::class, 'pipedrive_key');
    }
}
