<?php

namespace App\License\tests\Backend\Controllers\Admin;

use App\License\Controllers\Admin\ClientController;
use App\License\tests\Backend\LicenseTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class ClientControllerTest extends LicenseTestCase
{
    private ClientController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new ClientController();
    }

    #[Test]
    #[Group('license-admin')]
    public function view_clients_filters_and_formats_users(): void
    {
        $email = 'client-search-'.uniqid().'@example.test';
        $user = $this->createUser([
            'first_name' => 'License',
            'last_name' => 'SearchClient',
            'email' => $email,
        ]);

        $response = $this->controller->viewClients($this->moduleRequest([
            'search_query' => $email,
        ]));
        $json = $this->jsonContent($response);
        $row = $json['data']['data'][0];

        $this->assertSame($user->id, $row['client_id']);
        $this->assertSame('License SearchClient', $row['full_name']);
        $this->assertSame($email, $row['email']);
    }

    #[Test]
    #[Group('license-admin')]
    public function view_products_returns_product_select_payload(): void
    {
        $product = $this->createProduct(['name' => 'Selectable Product '.uniqid()]);

        $response = $this->controller->viewProducts();
        $json = $this->jsonContent($response);
        $ids = collect($json['data']['data'])->pluck('product_id');

        $this->assertTrue($ids->contains($product->id));
    }
}
