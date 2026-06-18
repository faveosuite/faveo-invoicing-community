<?php

namespace App\Model\Mailjob;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Mailjob\FaveoQueue> $extraFieldRelation
 * @property-read int|null $extra_field_relation_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueService whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueService whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueService whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueService whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class QueueService extends Model
{
    protected $table = 'queue_services';

    protected $fillable = ['name', 'short_name', 'status'];

    public function extraFieldRelation()
    {
        $related = FaveoQueue::class;

        return $this->hasMany($related, 'service_id');
    }

    public function getExtraField($key)
    {
        $value = '';
        $setting = $this->extraFieldRelation()->where('key', $key)->first();
        if ($setting) {
            return $setting->value;
        }

        return $value;
    }

    public function isActivate()
    {
        $check = true;
        $settings = $this->extraFieldRelation()->get();
        if ($settings->count() == 0) {
            return false;
        }

        return $check;
    }

    public function getQueueDetails(): array
    {
        $id = $this->attributes['id'];
        $name = $this->attributes['name'];
        $status = $this->attributes['status'];

        return [
            'id' => $id,
            'name' => [
                'text' => $name,
                'link' => ($name == 'Sync' || $name == 'Database') ? null : url('queue/'.$id),
            ],
            'status' => [
                'code' => (int) $status,
                'label' => $status == 1 ? __('message.active') : __('message.inactive'),
            ],
            'action' => [
                'type' => $status == 1 ? 'activated' : 'activate',
                'url' => url(sprintf('queue/%s/activate', $id)),
                'disabled' => (bool) $status,
            ],
        ];
    }
}
