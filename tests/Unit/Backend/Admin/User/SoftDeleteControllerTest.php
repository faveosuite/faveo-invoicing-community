<?php

namespace Tests\Unit\Backend\Admin\User;

use App\Auto_renewal;
use App\Comment;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\User;
use App\WhatsappIntegrationUser;
use Tests\DBTestCase;

class SoftDeleteControllerTest extends DBTestCase
{
    #[Group('softDelete')]
    public function test_soft_deleted_users_check_user_is_soft_deleted(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $user->delete();

        $data = $this->call('GET', 'soft-delete');
        json_decode($data->getContent())->data[0]->id;
        $this->assertSoftDeleted('users', ['id' => $user->id, 'email' => $user->email]);
    }

    #[Group('softDelete')]
    public function test_restore_user_check_soft_deleted_user_is_restored(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $user->delete();

        $data = $this->call('GET', 'clients/'.$user->id.'/restore');
        $data->assertSessionHas('success');
    }

    #[Group('softDelete')]
    public function test_permanent_delete_user_delete_user_permanently(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        WhatsappIntegrationUser::create(['user_id' => $user->id, 'waba_id' => 'wiuefh32843ry9',
            'phone_number_id' => 'fisufhiewfuhiu23', 'business_id' => 'fiowehfiu233',
            'user_callback_url' => 'iwehfowihfwef', 'access_token' => 'fiowehfoiwhef', 'order_id' => 'fiowhefowiefh', 'phone_number' => 'khfwiohfoihwefoifh']);
        $user->delete();
        $this->expectOutputRegex('/Deleted Successfully/');
        $this->call('DELETE', 'permanent-delete-client', ['select' => [$user->id]]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Group('softDelete')]
    public function test_permanent_delete_user_delete_invoice_order_commnet_permanently(): void
    {
        $this->withoutMiddleware();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::create(['name' => 'Helpdesk']);
        $invoice = Invoice::create(['user_id' => $user1->id, 'number' => '234435']);
        Comment::create(['user_id' => $user2->id, 'updated_by_user_id' => $user1->id, 'description' => 'TesComment']);
        $order = Order::create(['client' => $user1->id, 'order_status' => 'executed', 'product' => $product->id]);
        Auto_renewal::create(['user_id' => $user1->id, 'order_id' => $order->id, 'customer_id' => $user1->id,
            'invoice_number' => $invoice->number, 'payment_method' => 'Razorpay', 'payment_intent_id' => 1]);
        $user1->delete();
        $this->expectOutputRegex('/Deleted Successfully/');
        $this->call('DELETE', 'permanent-delete-client', ['select' => [$user1->id]]);
        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseMissing('invoices', ['user_id' => $user1->id]);
        $this->assertDatabaseMissing('orders', ['client' => $user1->id]);
        $this->assertDatabaseMissing('comments', ['updated_by_user_id' => $user1->id]);
        $this->assertDatabaseMissing('auto_renewals', ['user_id' => $user1->id]);
    }

    public function test_permanent_delete_user_fails_due_to_auto_renewal_not_deleted(): void
    {
        $this->withoutMiddleware();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::create(['name' => 'Helpdesk']);
        $invoice = Invoice::create(['user_id' => $user1->id, 'number' => '234435']);
        Comment::create(['user_id' => $user2->id, 'updated_by_user_id' => $user1->id, 'description' => 'TesComment']);
        $order = Order::create(['client' => $user1->id, 'order_status' => 'executed', 'product' => $product->id]);
        Auto_renewal::create(['user_id' => $user2->id, 'order_id' => $order->id, 'customer_id' => $user2->id,
            'invoice_number' => $invoice->number, 'payment_method' => 'Razorpay', 'payment_intent_id' => 1]);
        $user1->delete();
        $this->expectOutputRegex('/Deleted Successfully/');
        $this->call('DELETE', 'permanent-delete-client', ['select' => [$user1->id]]);
        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseMissing('invoices', ['user_id' => $user1->id]);
        $this->assertDatabaseMissing('orders', ['client' => $user1->id]);
        $this->assertDatabaseMissing('comments', ['updated_by_user_id' => $user1->id]);
        $this->assertDatabaseHas('auto_renewals', ['user_id' => $user2->id]);
    }
}
