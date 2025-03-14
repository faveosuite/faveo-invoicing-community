<?php

namespace Tests\Unit\Admin\User;

use App\Comment;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\User;
use Tests\DBTestCase;

class SoftDeleteControllerTest extends DBTestCase
{
    #[Group('softDelete')]
    public function test_softDeletedUsers_checkUserIsSoftDeleted()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $user->delete();
        $data = $this->call('GET', 'soft-delete');
        $idAfterDelete = json_decode($data->getContent())->data[0]->id;
        $this->assertSoftDeleted('users', ['id' => $user->id, 'email' => $user->email]);
    }

    #[Group('softDelete')]
    public function test_restoreUser_checkSoftDeletedUserIsRestored()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $user->delete();
        $data = $this->call('GET', 'clients/'.$user->id.'/restore');
        $data->assertSessionHas('success');
    }

    #[Group('softDelete')]
    public function test_permanentDeleteUser_deleteUserPermanently()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $user->delete();
        $data = $this->call('DELETE', 'permanent-delete-client', ['select' => [$user->id]]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Group('softDelete')]
    public function test_permanentDeleteUser_deleteInvoiceOrderCommnetPermanently()
    {
        $this->withoutMiddleware();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::create(['name' => 'Helpdesk']);
        $invoice = Invoice::create(['user_id' => $user1->id, 'number' => '234435']);
        $comment = Comment::create(['user_id' => $user2->id, 'updated_by_user_id' => $user1->id, 'description' => 'TesComment']);
        $order = Order::create(['client' => $user1->id, 'order_status' => 'executed', 'product' => $product->id]);
        $user1->delete();
        $data = $this->call('DELETE', 'permanent-delete-client', ['select' => [$user1->id]]);
        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseMissing('invoices', ['user_id' => $user1->id]);
        $this->assertDatabaseMissing('orders', ['client' => $user1->id]);
        $this->assertDatabaseMissing('comments', ['updated_by_user_id' => $user1->id]);
    }
}
