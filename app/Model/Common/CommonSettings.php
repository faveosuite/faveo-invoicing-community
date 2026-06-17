<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $option_name
 * @property string $option_value
 * @property string $status
 * @property string $optional_field
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommonSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommonSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommonSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommonSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommonSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommonSettings whereOptionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommonSettings whereOptionValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommonSettings whereOptionalField($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommonSettings whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CommonSettings whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CommonSettings extends Model
{
    use HasFactory;

    protected $table = 'common_settings';

    protected $fillable = [
        'option_name', 'option_value', 'status', 'optional_field',
    ];

    public function getStatus($option_name)
    {
        $status = '';
        $schema = $this->where('option_name', $option_name)->first();
        if ($schema) {
            return $schema->status;
        }

        return $status;
    }

    public function getOptionValue($option, $field = '')
    {
        $schema = $this->where('option_name', $option);
        if ($field != '') {
            return $schema->where('optional_field', $field)->first();
        }

        return $schema->get();
    }
}
