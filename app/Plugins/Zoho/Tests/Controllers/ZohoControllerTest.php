<?php

namespace App\Plugins\Zoho\Tests\Controllers;

use Exception;
use App\Plugins\Zoho\Controllers\ZohoController;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\Plugins\Zoho\Integrations\Crm\Controllers\ZohoCrmController;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ZohoControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_it_adds_user_to_zoho_campaigns_successfully()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $campaignsMock = $this->createMock(ZohoCampaignsController::class);
        $campaignsMock->expects($this->once())
            ->method('subscribe')
            ->with('test@example.com', 'newsletter');

        $this->instance(ZohoCampaignsController::class, $campaignsMock);

        $crmMock = $this->createMock(ZohoCrmController::class);
        $crmMock->expects($this->once())
            ->method('addUserDataToCrm')
            ->with('test@example.com');

        $this->instance(ZohoCrmController::class, $crmMock);

        $controller = new ZohoController($campaignsMock, $crmMock);
        $controller->addUserToZoho($user);
    }

    public function test_it_handles_campaigns_exception_gracefully()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $campaignsMock = $this->createMock(ZohoCampaignsController::class);
        $campaignsMock->expects($this->once())
            ->method('subscribe')
            ->willThrowException(new Exception('Campaigns API error'));

        $this->instance(ZohoCampaignsController::class, $campaignsMock);

        $crmMock = $this->createMock(ZohoCrmController::class);
        $crmMock->expects($this->once())
            ->method('addUserDataToCrm');

        $this->instance(ZohoCrmController::class, $crmMock);

        $controller = new ZohoController($campaignsMock, $crmMock);
        $controller->addUserToZoho($user);
    }

    public function test_it_handles_crm_exception_gracefully()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $campaignsMock = $this->createMock(ZohoCampaignsController::class);
        $crmMock = $this->createMock(ZohoCrmController::class);
        $crmMock->expects($this->once())
            ->method('addUserDataToCrm')
            ->willThrowException(new Exception('CRM API error'));

        $this->instance(ZohoCrmController::class, $crmMock);

        // Should not throw exception
        $controller = new ZohoController($campaignsMock, $crmMock);
        $controller->addUserToZoho($user);
    }
}
