<?php

namespace App\Model\Mailjob;

use Illuminate\Database\Eloquent\Model;

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
                'link' => ($name == 'Sync' || $name == 'Database') ? null : url('queue/' . $id),
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
