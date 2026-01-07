<?php

namespace App\Plugins\Zoho\Integrations\Campaigns\Controllers;

use App\Model\Order\InvoiceItem;
use App\Model\Product\Product;
use App\Plugins\Zoho\Controllers\ZohoBaseController;
use App\Plugins\Zoho\Controllers\ZohoSync;
use App\Plugins\Zoho\Integrations\Campaigns\Facades\ZohoCampaigns;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use App\User;
use Illuminate\Http\Request;

class ZohoCampaignsController extends ZohoBaseController
{
    protected Campaigns $campaigns;

    public function __construct()
    {
        $this->campaigns = (new Campaigns());
    }

    public function syncFields()
    {
        try {
            // Sync Topics
            $this->campaigns->syncTopics();

            // Sync Fields
            app(ZohoSync::class)->sync(
                platform: 'campaigns',
                module: 'Contacts',
                fields: $this->campaigns->contactFields()->toArray()
            );

            return successResponse('Campaigns fields and topics synced successfully');
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getCampaignsMappedFields()
    {
        return $this->getMappedFields('campaigns', 'Contacts');
    }

    public function getCampaignsContactFields()
    {
        return $this->getModulesFields('campaigns', 'Contacts');
    }

    public function subscribeCampaign(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
            ]);

            $this->subscribe($data['email'], 'newsletter');

            return successResponse('Subscribed successfully');
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    protected function subscribe(string $email, string $type): void
    {
        $zohoFields = ZohoFields::wherePlatform('campaigns')
            ->whereModule('Contacts')
            ->get();

        $mappings = ZohoFieldMappings::with('faveoLocalField')->get();

        $contactInfo = zohoMappedFields($zohoFields, $mappings, User::where('email', $email)->first());

        ZohoCampaigns::subscribe(
            $email,
            $contactInfo,
            null,
            $type
        );
    }

    public function updateSubscriberForProduct(int $productId, int $user_id, InvoiceItem $item)
    {
        $email = User::findOrFail($user_id)->email;

        $product = Product::find($productId);

        $type = $item->subtotal > 0 ? 'paid product' : 'free product';

        $this->subscribe($email, $type);

        ZohoCampaigns::attachTag($email, $product->name ?? $type);
    }
}
