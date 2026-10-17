<?php

namespace App\Model\Common;

use App\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property int $id
 * @property string $name
 * @property string $location
 * @property-read string $timezone_name
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone whereName($value)
 *
 * @mixin \Eloquent
 */
class Timezone extends BaseModel
{
    protected $table = 'timezone';

    protected $fillable = ['id', 'name', 'location'];

    public $timestamps = false;

    protected $appends = ['timezone_name'];

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function timezoneName(): Attribute
    {
        return Attribute::make(get: function (): string {
            $extractGMT = explode(' ', $this->location);

            return reset($extractGMT).' '.$this->name;
        });
    }
}
