<?php

namespace App\Plugins\Zoho\Integrations\Campaigns\Controllers;

use App\Plugins\Zoho\Controllers\ZohoBaseController;
use App\Plugins\Zoho\Controllers\ZohoSync;
use App\Plugins\Zoho\Integrations\Campaigns\Facades\ZohoCampaigns;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use App\Plugins\Zoho\Models\ZohoIntegration;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZohoCampaignsController extends ZohoBaseController
{
    protected ?Campaigns $campaigns = null;

    /**
     * Get the Campaigns instance lazily to avoid API calls during route registration.
     */
    protected function campaigns(): Campaigns
    {
        if (! $this->campaigns instanceof Campaigns) {
            $this->campaigns = new Campaigns;
        }

        return $this->campaigns;
    }

    public function syncFields(): JsonResponse
    {
        try {
            // Sync Topics
            $this->campaigns()->syncTopics();

            // Sync Fields
            $removed = resolve(ZohoSync::class)->sync(
                platform: 'campaigns',
                module: 'Contacts',
                fields: $this->campaigns()->contactFields()->toArray()
            );

            return successResponse(syncedMessage('Campaigns fields and topics synced successfully', $removed));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
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

    public function getLists(): JsonResponse
    {
        try {
            return successResponse('', [
                'lists' => $this->campaigns()->mailingLists(),
                'default_list_name' => config('zoho_campaigns.defaultListName'),
            ]);
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    public function saveList(Request $request): JsonResponse
    {
        $data = $request->validate([
            'list_key' => ['nullable', 'string'],
        ]);

        ZohoIntegration::wherePlatform('campaigns')->update(['list_key' => $data['list_key'] ?? null]);

        return successResponse(__('message.zoho_list_saved'));
    }

    /**
     * Subscribe the signed-in admin with the current mapping, so they can see in Zoho exactly what gets sent.
     */
    public function sendTest(): JsonResponse
    {
        /** @var User $admin */
        $admin = auth()->user();

        try {
            $this->subscribe($admin->email, 'newsletter');

            return successResponse(__('message.zoho_test_sent', ['email' => $admin->email]));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            // Admin-only endpoint: show Zoho's real reason so the mapping can be fixed.
            return errorResponse(__('message.zoho_test_failed', ['reason' => $exception->getMessage()]));
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

        // Guests can subscribe from the newsletter form — no account means only mapped defaults go along.
        $contactInfo = zohoMappedFields(
            $zohoFields,
            $mappings,
            User::where('email', $email)->first() ?? []
        );

        // Subscribe takes contact fields by display name ("First Name"), not field name ("firstname").
        $displayNames = $zohoFields->pluck('display_name', 'zoho_key');
        $contactInfo = collect($contactInfo)
            ->mapWithKeys(fn ($value, $key): array => [$displayNames[$key] ?? $key => $value])
            ->all();

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
