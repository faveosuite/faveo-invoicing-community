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
    ) {}

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

    public function testEvent(Request $request): JsonResponse
    {
        $event = $request->get('event');

        $productId = $request->get('product_id');

        $productType = $request->get('product_type');

        $user = User::where('email', 'sadha8122@gmail.com')->first();

        if (! $user) {
            return errorResponse('Demo user not found');
        }

        $productType === 'free'
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

            'purchase' => event(new OrderPlacedEvent(
                Invoice::whereHas('invoiceItem', fn (Builder $q) => $q->where('product_id', $productId))->latest()->firstOrFail()
            )),

            default => abort(400, 'Invalid event type'),
        };

        return successResponse(sprintf("Event '%s' triggered successfully", $event));
    }
}
