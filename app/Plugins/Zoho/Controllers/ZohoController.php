<?php

namespace App\Plugins\Zoho\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\Plugins\Zoho\Integrations\Crm\Controllers\ZohoCrmController;
use App\User;
use Logger;
use Throwable;

class ZohoController extends Controller
{
    public function __construct(
        private readonly ZohoCampaignsController $campaignsController,
        private readonly ZohoCrmController $crmController
    ) {
    }

    public function addUserToZoho(User $user): void
    {
        $email = $user->email;

        try {
            $this->campaignsController->subscribe($email, 'newsletter');
        } catch (Throwable $throwable) {
            Logger::exception($throwable);
        }

        try {
            $this->crmController->addUserDataToCrm($email);
        } catch (Throwable $throwable) {
            Logger::exception($throwable);
        }
    }
}
