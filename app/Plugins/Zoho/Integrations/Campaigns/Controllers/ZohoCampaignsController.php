<?php

namespace App\Plugins\Zoho\Integrations\Campaigns\Controllers;

use App\Plugins\Zoho\Controllers\ZohoBaseController;
use App\Plugins\Zoho\Controllers\ZohoSync;
use App\Plugins\Zoho\Integrations\Campaigns\Facades\ZohoCampaigns;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use App\User;
use Exception;
use Illuminate\Http\Request;

class ZohoCampaignsController extends ZohoBaseController
{
    protected ?Campaigns $campaigns = null;

    /**
     * Get the Campaigns instance lazily to avoid API calls during route registration.
     */
    protected function campaigns(): Campaigns
    {
        if (! $this->campaigns instanceof \App\Plugins\Zoho\Integrations\Campaigns\Controllers\Campaigns) {
            $this->campaigns = new Campaigns();
        }

        return $this->campaigns;
    }

    public function syncFields(): \Illuminate\Http\JsonResponse
    {
        try {
            // Sync Topics
            $this->campaigns()->syncTopics();

            // Sync Fields
            resolve(ZohoSync::class)->sync(
                platform: 'campaigns',
                module: 'Contacts',
                fields: $this->campaigns()->contactFields()->toArray()
            );

            return successResponse('Campaigns fields and topics synced successfully');
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getCampaignsMappedFields(): mixed
    {
        return $this->getMappedFields('campaigns', 'Contacts');
    }

    public function getCampaignsContactFields(): mixed
    {
        return $this->getModulesFields('campaigns', 'Contacts');
    }

    public function subscribeCampaign(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email'],
            ]);

            $this->subscribe($data['email'], 'newsletter');

            return successResponse('Subscribed successfully');
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function subscribe(string $email, string $type): void
    {
        $topicName = config('zoho_campaigns.topics.'.$type.'.name');

        if (! $topicName) {
            return;
        }

        $zohoFields = ZohoFields::wherePlatform('campaigns')
            ->whereModule('Contacts')
            ->get();

        $mappings = ZohoFieldMappings::with('faveoLocalField')->get();

        /** @var \App\User $zohoUser */
        $zohoUser = User::where('email', $email)->first();
        $contactInfo = zohoMappedFields(
            $zohoFields, // @phpstan-ignore argument.type
            $mappings, // @phpstan-ignore argument.type
            $zohoUser
        );

        ZohoCampaigns::subscribe(
            $email,
            $contactInfo
        );
    }

    public function subscribeWithTag(string $email, string $type, string $tag): void
    {
        $this->subscribe($email, $type);

        ZohoCampaigns::attachTag($email, $tag);
    }
}
