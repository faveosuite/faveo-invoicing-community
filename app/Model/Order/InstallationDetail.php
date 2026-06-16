<?php

namespace App\Model\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

class InstallationDetail extends Model
{
    use HasFactory;

    protected $table = 'installation_details';

    protected $fillable = ['installation_path', 'installation_ip', 'version', 'last_active', 'order_id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    #[Override]
    public function delete()
    {
        $this->order()->delete();

        return parent::delete();
    }
}
