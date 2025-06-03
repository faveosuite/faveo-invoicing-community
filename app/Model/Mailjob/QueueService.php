<?php

namespace App\Model\Mailjob;

use Illuminate\Database\Eloquent\Model;

class QueueService extends Model
{
    protected $table = 'queue_services';

    protected $fillable = ['name', 'short_name', 'status'];

    public function extraFieldRelation()
    {
        $related = \App\Model\Mailjob\FaveoQueue::class;

        return $this->hasMany($related, 'service_id');
    }

    public function getExtraField($key)
    {
        $value = '';
        $setting = $this->extraFieldRelation()->where('key', $key)->first();
        if ($setting) {
            $value = $setting->value;
        }

        return $value;
    }

    public function getName()
    {
        $name = $this->attributes['name'];
        $id = $this->attributes['id'];
        if ($name == 'Sync' or $name == 'Database') {
            $html = $name;
        } else {
            $html = '<a href='.url('queue/'.$id).'>'.$name.'</a>';
        }

        return $html;
    }

    public function getStatus()
    {
        $status = $this->attributes['status'];
        $html = "<span class='badge badge-primary' style='background-color:crimson !important;'>". __('message.inactive') ."</span>";
        if ($status == 1) {
            $html = "<span class='badge badge-primary' style='background-color:darkcyan !important;'>". __('message.active') ."</span>";
        }

        return $html;
    }

    public function getAction()
    {
        $id = $this->attributes['id'];
        $status = $this->attributes['status'];
        $html = '<form method="post" action='.url('queue/'.$id.'/activate').'>'.'<input type="hidden" name="_token" value='.\Session::token().'>'.'
                                <button type="submit"  class="btn btn-secondary btn-sm btn-xs"><i class="fa fa-check-circle">&nbsp;&nbsp;</i>'.\Lang::get('message.activate').'</button></form>';

        if ($status == 1) {
            $html = "<a href='#' class='btn btn-secondary btn-sm btn-xs disabled' ><i class='fa fa-check-circle'>&nbsp;&nbsp;</i>".\Lang::get('message.activate').'</a>';
        }

        return $html;
    }

    public function isActivate()
    {
        $check = true;
        $settings = $this->extraFieldRelation()->get();
        if ($settings->count() == 0) {
            $check = false;
        }

        return $check;
    }
}
