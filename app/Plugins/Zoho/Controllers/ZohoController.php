<?php

namespace App\Plugins\Zoho\Controllers;

use App\Events\OrderPlacedEvent;
use App\Http\Controllers\Controller;
use App\Jobs\AddUserToExternalService;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\Plugins\Zoho\Integrations\Crm\Controllers\ZohoCrmController;
use App\User;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
