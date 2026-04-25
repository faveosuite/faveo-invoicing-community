<?php

namespace Razorpay\Tests;
require_once realpath(__DIR__ . "/../vendor/autoload.php");

use Razorpay\Api\Api;
use Dotenv\Dotenv;

if (class_exists('Dotenv'))
{  
 $dotenv = Dotenv::createImmutable(__DIR__);
 $dotenv->load();
}


class PartnerTest extends TestCase
{
    protected $instance;

    protected static $account_id;

    protected static $stakeholder_id;

    protected static $product_id;

    protected static $webhook_id;

    protected $accId = "acc_M83Uw27KXuC7c8";

    protected $stkId = "sth_M83WuwmrCFa55g";

    protected $docId = "doc_NBpjajKtxrMDg7";

    public function setUp(): void
    {
        $apiKey = getenv("RAZORPAY_API_KEY") ? getenv("RAZORPAY_PARTNER_API_KEY") : "";
        $apiSecret = getenv("RAZORPAY_API_SECRET") ? getenv("RAZORPAY_PARTNER_API_SECRET") : "";
        $this->instance = new Api( $apiKey, $apiSecret);
    }
    

    public function testCreateAccount()
    {
        $account = $this->instance->account->create($this->accountAttributes());
         
        self::$account_id = $account['id'];

        $this->assertTrue(is_array($account->toArray()));
        $this->assertTrue(is_array((array) $account['legal_info']));
    }

    public function testCreateStakerholder()
    {
        $stakeholder = $this->instance->account->fetch(self::$account_id)->stakeholders()->create($this->stakeholderAttributes());

        $this->assertTrue(is_array($stakeholder->toArray()));

        self::$stakeholder_id = $stakeholder['id'];

        $this->assertTrue(is_array((array) $stakeholder['phone']));
    }

    public function testFetchStakerholder()
    {
        $stakeholder = $this->instance->account->fetch(self::$account_id)->stakeholders()->fetch(self::$stakeholder_id);

        $this->assertTrue(is_array($stakeholder->toArray()));

        $this->assertTrue(is_array((array) $stakeholder['phone']));
    }

    public function testFetchAllStakerholder()
    {
        $stakeholder = $this->instance->account->fetch(self::$account_id)->stakeholders()->all();

        $this->assertTrue(is_array($stakeholder->toArray()));

        $this->assertTrue(is_array((array) $stakeholder['items']));
    }

    public function testEditStakerholder()
    {
        $stakeholder = $this->instance->account->fetch(self::$account_id)->stakeholders()->edit(self::$stakeholder_id, $this->stakeholderAttributes());

        $this->assertTrue(is_array($stakeholder->toArray()));

        $this->assertTrue(is_array((array) $stakeholder['phone']));
    }

   public function testProductConfiguration(){
        $product = $this->instance->account->fetch(self::$account_id)->products()->requestProductConfiguration([
            "product_name" => "payment_gateway",
            "tnc_accepted" => true,
            "ip" => "233.233.233.234"
        ]);
        self::$product_id = $product['id'];
        $this->assertTrue(is_array($product->toArray()));
        $this->assertTrue(is_array((array) $product['requirements']));
    }

    public function testProductFetch(){
        $product = $this->instance->account->fetch(self::$account_id)->products()->fetch(self::$product_id);
        $this->assertTrue(is_array($product->toArray()));
        $this->assertTrue(is_array((array) $product['requirements']));
    }

    public function testProductEdit(){
        $product = $this->instance->account->fetch(self::$account_id)->products()->edit(self::$product_id,[
            "tnc_accepted" => true,
            "ip" => "233.233.233.224"
        ]);

        $this->assertTrue(is_array($product->toArray()));
    }

    public function testFetchTnc()
    {
        $webhook = $this->instance->product->fetchTnc("payments");

        $this->assertTrue(is_array($webhook->toArray()));
    }

    public function testWebhookCreate()
    {
        $webhook = $this->instance->account->fetch(self::$account_id)->webhooks()->create($this->webhookAttributes());
        self::$webhook_id = $webhook['id'];
        
        $this->assertTrue(is_array($webhook->toArray()));
        
        $this->assertTrue(is_array((array) $webhook['id']));
    }

    public function testWebhookFetch()
    {
        $webhook = $this->instance->account->fetch(self::$account_id)->webhooks()->fetch(self::$webhook_id);
        
        $this->assertTrue(is_array($webhook->toArray()));
        
        $this->assertTrue(is_array((array) $webhook['id']));
    }

    public function testFetchAllWebhook()
    {
        $webhook = $this->instance->account->fetch(self::$account_id)->webhooks()->all();

        $this->assertTrue(is_array($webhook->toArray()));

        $this->assertTrue(is_array((array) $webhook['items']));
    }

    public function testEditWebhook()
    {
        $webhook = $this->instance->account->fetch(self::$account_id)->webhooks()->edit([
            "url" => "https://www.linkedin.com",
            "events" => ["refund.created"],
        ],self::$webhook_id);

        $this->assertTrue(is_array($webhook->toArray()));

        $this->assertTrue(is_array((array) $webhook['events']));
    }

    public function testDeleteWebhook()
    {
        $webhook = $this->instance->account->fetch(self::$account_id)->webhooks()->delete(self::$webhook_id);
        
        $this->assertTrue(is_array($webhook->toArray()));
    }

    public function testDeleteAccount()
    {
        $account = $this->instance->account->fetch(self::$account_id)->delete();
        
        $this->assertTrue(is_array($account->toArray()));
        
        $this->assertTrue(is_array((array) $account['legal_info']));
    }

    public function testFetchAccount()
    {
        $account = $this->instance->account->fetch(self::$account_id);
        
        $this->assertTrue(is_array($account->toArray()));
        
        $this->assertTrue(is_array((array) $account['legal_info']));
    }

    public function testEditAccount()
    {
        $request = ["customer_facing_business_name" => "Ltd"];

        $account = $this->instance->account->fetch(self::$account_id)->edit($request);
        
        $this->assertTrue(is_array($account->toArray()));
        
        $this->assertTrue(is_array((array) $account['legal_info']));
    }

    public function accountAttributes(){
        return $arrayVar = [
            "email" => "gauriagain".time()."@example.org",
            "phone" => "9000090000",
            "legal_business_name" => "Acme Corp",
            "business_type" => "partnership",
            "customer_facing_business_name" => "Example",
            "profile" => [
                "category" => "healthcare",
                "subcategory" => "clinic",
                "description" => "Healthcare E-commerce platform",
                "addresses" => [
                    "operation" => [
                        "street1" => "507, Koramangala 6th block",
                        "street2" => "Kormanagala",
                        "city" => "Bengaluru",
                        "state" => "Karnataka",
                        "postal_code" => 560047,
                        "country" => "IN",
                    ],
                    "registered" => [
                        "street1" => "507, Koramangala 1st block",
                        "street2" => "MG Road",
                        "city" => "Bengaluru",
                        "state" => "Karnataka",
                        "postal_code" => 560034,
                        "country" => "IN",
                    ],
                ],
                "business_model" =>
                    "Online Clothing ( men, women, ethnic, modern ) fashion and lifestyle, accessories, t-shirt, shirt, track pant, shoes.",
            ],
            "legal_info" => ["pan" => "AAACL1234C", "gst" => "18AABCU9603R1ZM"],
            "brand" => ["color" => "FFFFFF"],
            "notes" => ["internal_ref_id" => "123123"],
            "contact_name" => "Gaurav Kumar",
            "contact_info" => [
                "chargeback" => ["email" => "cb@example.org"],
                "refund" => ["email" => "cb@example.org"],
                "support" => [
                    "email" => "support@example.org",
                    "phone" => "9999999998",
                    "policy_url" => "https://www.google.com",
                ],
            ],
            "apps" => [
                "websites" => ["https://www.example.org"],
                "android" => [["url" => "playstore.example.org", "name" => "Example"]],
                "ios" => [["url" => "appstore.example.org", "name" => "Example"]],
            ],
        ];
    }

    public function stakeholderAttributes(){
        return $arrayVar = [
            "percentage_ownership" => 10,
            "name" => "Gaurav Kumar",
            "email" => "gaurav". time() .".kumar@example.com",
            "relationship" => ["director" => true, "executive" => false],
            "phone" => ["primary" => "7474747474", "secondary" => "7474747474"],
            "addresses" => [
                "residential" => [
                    "street" => "506, Koramangala 1st block",
                    "city" => "Bengaluru",
                    "state" => "Karnataka",
                    "postal_code" => "560034",
                    "country" => "IN",
                ],
            ],
            "kyc" => ["pan" => "AVOPB1111K"],
            "notes" => ["random_key_by_partner" => "random_value"],
        ];
    }

    public function webhookAttributes(){
        return $arrayVar = [
            "url" => "https://google.com",
            "alert_email" => "gaurav.kumar@example.com",
            "secret" => "12345",
            "events" => [
                "payment.authorized",
                "payment.failed",
                "payment.captured",
                "payment.dispute.created",
                "refund.failed",
                "refund.created",
            ],
        ];
    }

    public function testAccountFetchDocument()
    {
        $data = $this->instance->account->fetch($this->accId)->fetchAccountDoc();

        $this->assertTrue(is_array($data->toArray()));

    }

    public function testStakeholderFetchDocument()
    {
        $data = $this->instance->account->fetch($this->accId)->stakeholders()->fetchStakeholderDoc($this->stkId);

        $this->assertTrue(is_array($data->toArray()));

    }

    public function testFetchDocument()
    {
        $data = $this->instance->document->fetch($this->docId);

        $this->assertTrue(is_array($data->toArray()));

        $this->assertArrayHasKey("entity", $data->toArray());
    }
}
