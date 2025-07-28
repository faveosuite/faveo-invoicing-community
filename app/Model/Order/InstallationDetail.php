<?php

namespace App\Model\Order;

use Illuminate\Database\Eloquent\Model;

class InstallationDetail extends Model
{
    protected $table = 'installation_details';

    protected $fillable = ['installation_path', 'installation_ip', 'version', 'last_active', 'order_id'];

    public function order()
    {
        return $this->belongsTo(\App\Model\Order\Order::class);
    }

    public function delete()
    {
        $this->order()->delete();

        return parent::delete();
    }
}
