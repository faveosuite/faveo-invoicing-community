<?php

namespace App\Listeners;

use App\Events\UserOrderDelete;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\FaveoCloud;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;

class CloudDeletion implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserOrderDelete $event): void
    {
        (new TenantController(new Client, new FaveoCloud))->destroyTenant(new Request(['id' => $event->domain, 'orderId' => $event->order_id]));
    }
}
