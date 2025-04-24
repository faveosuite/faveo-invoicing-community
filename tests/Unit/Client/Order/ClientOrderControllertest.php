<?php

namespace Tests\Unit\Client\Order;

use Tests\DBTestCase;
use App\Http\Controllers\Front\ClientController;
use App\User;
use App\Model\Order\Order;


    class ClientOrderControllertest extends DBTestCase
{
/** @group order  */

    public function  test_my_orders_datatable_sends_data(){
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order=Order::factory()->create(['client'=>$user->id]);
            $response=$this->call('get','get-my-orders',['updated_ends_at'=>'expired']);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'draw',
                'recordsTotal',
                'recordsFiltered',
                'data' => [
                    '*' => [
                        'product_name',
                        'date',
                        'number',
                        'agents',
                        'expiry',
                        'Action',
                    ],
                ],
            ]);
    }




}
