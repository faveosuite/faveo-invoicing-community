<?php

declare(strict_types=1);

namespace App\Plugins\Zoho\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $field_key
 * @property string $display_name
 * @property string $field_type
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoLocalFields newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoLocalFields newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoLocalFields query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoLocalFields whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoLocalFields whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoLocalFields whereFieldKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoLocalFields whereFieldType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoLocalFields whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoLocalFields whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoLocalFields whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FaveoLocalFields extends Model
{
    protected $table = 'faveo_local_fields';

    protected $guarded = [];
}
