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

    public function subscribe(string $email, string $type): void
    {
        $topicName = config('zoho_campaigns.topics.' . $type . '.name');

        if (! $topicName) {
            return;
        }

        $zohoFields = ZohoFields::wherePlatform('campaigns')
            ->whereModule('Contacts')
            ->get();

        $mappings = ZohoFieldMappings::with('faveoLocalField')->get();

        $contactInfo = zohoMappedFields($zohoFields, $mappings, User::where('email', $email)->first());

        ZohoCampaigns::subscribe(
            $email,
            $contactInfo,
            null,
            $topicName
        );
    }

    public function subscribeWithTag(string $email, string $type, string $tag): void
    {
        $this->subscribe($email, $type);

        ZohoCampaigns::attachTag($email, $tag);
    }
}
