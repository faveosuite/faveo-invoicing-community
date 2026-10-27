<?php

namespace App\Plugins\Zoho\Integrations\Crm\Controllers;

use App\Plugins\Zoho\Controllers\ZohoBaseController;
use App\Plugins\Zoho\Controllers\ZohoSync;
use App\Plugins\Zoho\Integrations\Crm\Facades\ZohoCrm;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZohoCrmController extends ZohoBaseController
{
    protected ?Crm $crm = null;

    /**
     * Get the Crm instance lazily to avoid API calls during route registration.
     */
    protected function crm(): Crm
    {
        if (! $this->crm instanceof Crm) {
            $this->crm = new Crm;
        }

        return $this->crm;
    }

    public function syncFields(): JsonResponse
    {
        try {
            $removed = [];

            foreach (['Contacts', 'Accounts'] as $module) {
                $removed = [...$removed, ...resolve(ZohoSync::class)->sync(
                    platform: 'crm',
                    module: $module,
                    fields: $this->crm()->fields($module)->toArray()
                )];
            }

            return successResponse(syncedMessage('CRM fields synced successfully', $removed));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    public function getCrmMappedFields(mixed $module): mixed
    {
        return $this->getMappedFields('crm', ucfirst(strtolower((string) $module)));
    }

    public function getCrmContactsFields(): mixed
    {
        return $this->getModulesFields('crm', 'Contacts');
    }

    public function getCrmAccountsFields(): mixed
    {
        return $this->getModulesFields('crm', 'Accounts');
    }

    /**
     * Create or update a CRM contact.
     */
    public function updateToZohoCrm(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email'],
            ]);

            $this->addUserDataToCrm($data['email']);

            return successResponse('CRM contact created successfully');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return errorResponse($exception->getMessage());
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    /**
     * Push the signed-in admin with the current mapping, so they can see in Zoho exactly what gets sent.
     */
    public function sendTest(): JsonResponse
    {
        /** @var User $admin */
        $admin = auth()->user();

        try {
            $sent = $this->addUserDataToCrm($admin->email);

            if ($sent === []) {
                return errorResponse(__('message.zoho_test_nothing_mapped'));
            }

            return successResponse(__('message.zoho_test_sent_modules', ['email' => $admin->email, 'modules' => implode(', ', $sent)]));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            // Admin-only endpoint: show Zoho's real reason so the mapping can be fixed.
            return errorResponse(__('message.zoho_test_failed', ['reason' => $exception->getMessage()]));
        }
    }

    /**
     * @return list<string> The modules that had mapped data and were sent.
     */
    public function addUserDataToCrm(string $email): array
    {
        return array_values(array_filter(
            ['Contacts', 'Accounts'],
            fn (string $module): bool => $this->insertModuledata($module, $email)
        ));
    }

    /**
     * @return bool Whether anything was mapped (and so sent) for this module.
     */
    protected function insertModuledata(string $module, string $email): bool
    {
        $user = User::where('email', $email)->firstOrFail();

        $zohoFields = ZohoFields::wherePlatform('crm')
            ->whereModule($module)
            ->get();

        $mappings = ZohoFieldMappings::with('faveoLocalField')->get();

        $recordData = zohoMappedFields(
            $zohoFields,
            $mappings,
            $user
        );

        if ($recordData === []) {
            return false;
        }

        ZohoCrm::upsert($module, $recordData);

        return true;
    }
}
