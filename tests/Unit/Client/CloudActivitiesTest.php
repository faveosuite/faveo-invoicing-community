<?php

namespace Tests\Unit\Client;

use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\Model\Common\FaveoCloud;
use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Order\InstallationDetail;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Tests\DBTestCase;

class CloudActivitiesTest extends DBTestCase
{


    public function test_cloud_agents_change_plan_ended(){
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype=LicenseType::create(['name'=>'DevelopmentLicense']);
        $licensepermissiontype=LicensePermission::create(['Can be Downloaded']);
        LicensePermission::create(['Generate License Expiry Date']);
        LicensePermission::create(['Generate Updates Expiry Date']);
        LicensePermission::create(['Allow Downloads Before Updates Expire']);
        $permissionid=[
            0 => "1",
            1 => "2",
            2 => "3",
            3 => "4",
            6=>'6',
        ];
        $licensetype->permissions()->attach($permissionid);

        $product = Product::create(['name' => 'Helpdesk Advance','description'=>'goodProduct','type'=>$licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'currency'=>'INR','add_price'=>5000]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>'']);
        $response=$this->call('POST','get-agent-inc-dec-cost',['number'=>5,'oldAgents'=>3,'orderId'=>$order->id]);
        $priceToPay=currencyFormat($planPrice->add_price*5,'INR',true);
        $content=$response->json();
        $response->assertStatus(200);
        $this->assertEquals($content['priceToPay'],$priceToPay);
    }


    public function test_cloud_agents_when_plan_not_ended(){
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype=LicenseType::create(['name'=>'DevelopmentLicense']);
        $licensepermissiontype=LicensePermission::create(['Can be Downloaded']);
        LicensePermission::create(['Generate License Expiry Date']);
        LicensePermission::create(['Generate Updates Expiry Date']);
        LicensePermission::create(['Allow Downloads Before Updates Expire']);
        $permissionid=[
            0 => "1",
            1 => "2",
            2 => "3",
            3 => "4",
            6=>'6',
        ];
        $licensetype->permissions()->attach($permissionid);

        $product = Product::create(['name' => 'Helpdesk Advance','description'=>'goodProduct','type'=>$licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'currency'=>'INR','add_price'=>5000]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>Carbon::now()->addDays(30)]);
        $response=$this->call('POST','get-agent-inc-dec-cost',['number'=>5,'oldAgents'=>3,'orderId'=>$order->id]);
        $response->assertStatus(200);
        $content=$response->json();
        $this->assertNotEquals($content['priceToPay'],$content['totalPrice']);
    }

    public function test_cloud_when_no_of_agents_less_then_old_agents(){
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype=LicenseType::create(['name'=>'DevelopmentLicense']);
        $licensepermissiontype=LicensePermission::create(['Can be Downloaded']);
        LicensePermission::create(['Generate License Expiry Date']);
        LicensePermission::create(['Generate Updates Expiry Date']);
        LicensePermission::create(['Allow Downloads Before Updates Expire']);
        $permissionid=[
            0 => "1",
            1 => "2",
            2 => "3",
            3 => "4",
            6=>'6',
        ];
        $licensetype->permissions()->attach($permissionid);

        $product = Product::create(['name' => 'Helpdesk Advance','description'=>'goodProduct','type'=>$licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'currency'=>'INR','add_price'=>5000]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>'']);
        $response=$this->call('POST','get-agent-inc-dec-cost',['number'=>3,'oldAgents'=>5,'orderId'=>$order->id]);
        $priceToPay=currencyFormat($planPrice->add_price*3,'INR',true);
        $content=$response->json();
        $response->assertStatus(200);
        $this->assertEquals($content['priceToPay'],$priceToPay);
    }

    public function test_when_days_are_more_less_no_of_agents(){
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype=LicenseType::create(['name'=>'DevelopmentLicense']);
        $licensepermissiontype=LicensePermission::create(['Can be Downloaded']);
        LicensePermission::create(['Generate License Expiry Date']);
        LicensePermission::create(['Generate Updates Expiry Date']);
        LicensePermission::create(['Allow Downloads Before Updates Expire']);
        $permissionid=[
            0 => "1",
            1 => "2",
            2 => "3",
            3 => "4",
            6=>'6',
        ];
        $licensetype->permissions()->attach($permissionid);

        $product = Product::create(['name' => 'Helpdesk Advance','description'=>'goodProduct','type'=>$licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'currency'=>'INR','add_price'=>5000]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>Carbon::now()->addDays(80)]);
        $response=$this->call('POST','get-agent-inc-dec-cost',['number'=>3,'oldAgents'=>5,'orderId'=>$order->id]);
        $content=$response->json();
        $response->assertStatus(200);
        $this->assertEquals($content['priceToPay'],'₹0');
    }

//to work
    public function  test_cloud_update_no_of_agents(){
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype=LicenseType::create(['name'=>'DevelopmentLicense']);
        $licensepermissiontype=LicensePermission::create(['Can be Downloaded']);
        LicensePermission::create(['Generate License Expiry Date']);
        LicensePermission::create(['Generate Updates Expiry Date']);
        LicensePermission::create(['Allow Downloads Before Updates Expire']);
        $permissionid=[
            0 => "1",
            1 => "2",
            2 => "3",
            3 => "4",
            6=>'6',
        ];
        $licensetype->permissions()->attach($permissionid);

        $product = Product::create(['name' => 'Helpdesk Advance','description'=>'goodProduct','type'=>$licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id]);
        $installationDetail=InstallationDetail::create(['order_id'=>$order->id,'installation_path'=>'/path']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'currency'=>'INR','add_price'=>5000]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>Carbon::now()->addDays(80)]);
        $cloud=FaveoCloud::create(['cloud_central_domain'=>'https://santhanu.com','cloud_cname'=>'santhanu.com']);
        $request=new Request(['newAgents'=>5,'product_id'=>$product->id,'subId'=>$subscription->id,'orderId'=>$order->id]);
        $client=new Client();
        $cloudActivities = new CloudExtraActivities($client,$cloud);

    }


    public function test_cloud_plan_old_price_less_then_new_price(){
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype=LicenseType::create(['name'=>'DevelopmentLicense']);
        $licensepermissiontype=LicensePermission::create(['Can be Downloaded']);
        LicensePermission::create(['Generate License Expiry Date']);
        LicensePermission::create(['Generate Updates Expiry Date']);
        LicensePermission::create(['Allow Downloads Before Updates Expire']);
        $permissionid=[
            0 => "1",
            1 => "2",
            2 => "3",
            3 => "4",
            6=>'6',
        ];
        $licensetype->permissions()->attach($permissionid);

        $product = Product::create(['name' => 'Helpdesk Advance','description'=>'goodProduct','type'=>$licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id,'serial_key'=>'eyJpdiI6IkpI0003']);
        $installationDetail=InstallationDetail::create(['order_id'=>$order->id,'installation_path'=>'/path']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        $plan2=Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $product->id, 'days' => 130]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'currency'=>'INR','add_price'=>5000]);
        $planPrice2=PlanPrice::factory()->create(['plan_id'=>$plan2->id,'currency'=>'INR','add_price'=>5000]);

        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>Carbon::now()->addDays(65)]);
        $subscription = Subscription::create(['plan_id' => $plan2->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>Carbon::now()->addDays(130)]);
        $response=$this->call('POST','get-cloud-upgrade-cost',['agents'=>5,'plan'=>$plan2->id,'orderId'=>$order->id]);
        $content=$response->json();
        $this->assertEquals('₹10,038',$content['price_to_be_paid']);
        $this->assertEquals('₹24,807',$content['pricenewplan']);
        $this->assertEquals('₹5,000',$content['priceperagent']);
    }

    public function test_cloud_plan_old_price_equal_to_new_price(){
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype=LicenseType::create(['name'=>'DevelopmentLicense']);
        $licensepermissiontype=LicensePermission::create(['Can be Downloaded']);
        LicensePermission::create(['Generate License Expiry Date']);
        LicensePermission::create(['Generate Updates Expiry Date']);
        LicensePermission::create(['Allow Downloads Before Updates Expire']);
        $permissionid=[
            0 => "1",
            1 => "2",
            2 => "3",
            3 => "4",
            6=>'6',
        ];
        $licensetype->permissions()->attach($permissionid);

        $product = Product::create(['name' => 'Helpdesk Advance','description'=>'goodProduct','type'=>$licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id,'serial_key'=>'eyJpdiI6IkpI0003']);
        $installationDetail=InstallationDetail::create(['order_id'=>$order->id,'installation_path'=>'/path']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        $plan2=Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $product->id, 'days' => 130]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'currency'=>'INR','add_price'=>5000]);
        $planPrice2=PlanPrice::factory()->create(['plan_id'=>$plan2->id,'currency'=>'INR','add_price'=>3000]);

        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>Carbon::now()->addDays(65)]);
        $subscription = Subscription::create(['plan_id' => $plan2->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>Carbon::now()->addDays(130)]);
        $response=$this->call('POST','get-cloud-upgrade-cost',['agents'=>5,'plan'=>$plan2->id,'orderId'=>$order->id]);
        $content=$response->json();
        $this->assertEquals('₹0',$content['price_to_be_paid']);
        $this->assertEquals('₹0',$content['pricenewplan']);
        $this->assertEquals('₹3,000',$content['priceperagent']);
    }


    public function test_cloud_plan_old_price_greater_than_new_price(){
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $licensetype=LicenseType::create(['name'=>'DevelopmentLicense']);
        $licensepermissiontype=LicensePermission::create(['Can be Downloaded']);
        LicensePermission::create(['Generate License Expiry Date']);
        LicensePermission::create(['Generate Updates Expiry Date']);
        LicensePermission::create(['Allow Downloads Before Updates Expire']);
        $permissionid=[
            0 => "1",
            1 => "2",
            2 => "3",
            3 => "4",
            6=>'6',
        ];
        $licensetype->permissions()->attach($permissionid);
        $product = Product::create(['name' => 'Helpdesk Advance','description'=>'goodProduct','type'=>$licensetype->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id,'serial_key'=>'eyJpdiI6IkpI0005']);
        $installationDetail=InstallationDetail::create(['order_id'=>$order->id,'installation_path'=>'/path']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        $plan2=Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $product->id, 'days' => 130]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'currency'=>'INR','add_price'=>5000]);
        $planPrice2=PlanPrice::factory()->create(['plan_id'=>$plan2->id,'currency'=>'INR','add_price'=>3000]);

        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>Carbon::now()->addDays(65)]);
        $subscription = Subscription::create(['plan_id' => $plan2->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>Carbon::now()->addDays(130)]);
        $response=$this->call('POST','get-cloud-upgrade-cost',['agents'=>5,'plan'=>$plan2->id,'orderId'=>$order->id]);
        $content=$response->json();
        $this->assertEquals('₹9,730',$content['discount']);
    }

public function test_cloud_upgrade_downgrade_plan(){
    $user=User::factory()->create();
    $this->actingAs($user);
    $this->withoutMiddleware();
    $licensetype=LicenseType::create(['name'=>'DevelopmentLicense']);
    $licensepermissiontype=LicensePermission::create(['Can be Downloaded']);
    LicensePermission::create(['Generate License Expiry Date']);
    LicensePermission::create(['Generate Updates Expiry Date']);
    LicensePermission::create(['Allow Downloads Before Updates Expire']);
    $permissionid=[
        0 => "1",
        1 => "2",
        2 => "3",
        3 => "4",
        6=>'6',
    ];
    $licensetype->permissions()->attach($permissionid);
    $product = Product::create(['name' => 'Helpdesk Advance','description'=>'goodProduct','type'=>$licensetype->id]);
    $invoice = Invoice::factory()->create(['user_id' => $user->id]);
    $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
    $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
        'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id,'serial_key'=>'eyJpdiI6IkpI0005']);
    $installationDetail=InstallationDetail::create(['order_id'=>$order->id,'installation_path'=>'/path']);
    $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
    $plan2=Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $product->id, 'days' => 65]);
    $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'currency'=>'INR','add_price'=>3000]);
    $planPrice2=PlanPrice::factory()->create(['plan_id'=>$plan2->id,'currency'=>'INR','add_price'=>5000,'no_of_agents'=>5]);

    $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
        'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>Carbon::now()->addDays(65)]);
    $subscription1 = Subscription::create(['plan_id' => $plan2->id, 'order_id' => $order->id, 'product_id' => $product->id,
        'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>Carbon::now()->addDays(65)]);

    $response=$this->call('POST','upgradeDowngradeCloud',['id'=>$plan2->id,'orderId'=>$order->id,'agents'=>$planPrice2->no_of_agents]);
    $response->assertStatus(200);
    $response->assertJson(['redirectTo'=>url('checkout')]);
}


}


