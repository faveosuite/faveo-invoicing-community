<?php

declare(strict_types=1);

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $field_name
 * @property string|null $field_key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PipedriveField|null $pipedrive
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
     * @use HasFactory<Factory>
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
     * @return BelongsTo<PipedriveField, $this>
     */
    public function pipedrive(): BelongsTo
    {
        return $this->belongsTo(PipedriveField::class, 'pipedrive_key');
    }
}
