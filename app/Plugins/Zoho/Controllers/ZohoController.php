<?php

namespace App\Plugins\Zoho\Controllers;

use Throwable;
use Logger;
use App\Http\Controllers\Common\ExternalServiceController;
use App\Http\Controllers\Controller;
use App\Jobs\AddUserToExternalService;
use App\Model\Order\InvoiceItem;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\Plugins\Zoho\Integrations\Crm\Controllers\ZohoCrmController;
use App\User;
use Illuminate\Http\Request;

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
        } catch (Throwable $e) {
            Logger::exception($e);
        }

        try {
            $this->crmController->addUserDataToCrm($email);
        } catch (Throwable $e) {
            Logger::exception($e);
        }
    }

    public function testEvent(Request $request)
    {
        $event = $request->get('event');

        $productId = $request->get('product_id');

        $productType = $request->get('product_type');

        $user = User::where('email', 'sadha8122@gmail.com')->first();

        if (! $user) {
            return errorResponse('Demo user not found');
        }

        $item = $productType === 'free'
            ? InvoiceItem::where('product_id', $productId)
                ->where('subtotal', 0)
                ->first()
            : InvoiceItem::where('product_id', $productId)
                ->where('subtotal', '>', 0)
                ->first();

        match ($event) {
            'register' => dispatch(new AddUserToExternalService($user, 'register')),

            'newsletter' => $this->campaignsController
                ->subscribeCampaign(new Request([
                    'email' => $user->email,
                ])),

            'purchase' => resolve(ExternalServiceController::class)->subscribeForProductsUpdates($productId, $user->id, $item),

            default => abort(400, 'Invalid event type'),
        };

        return successResponse("Event '{$event}' triggered successfully");
    }
}
