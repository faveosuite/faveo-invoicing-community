<?php

namespace App\Plugins\Zoho\Integrations\Crm\Controllers;

use App\Plugins\Zoho\Controllers\ZohoBaseController;
use App\Plugins\Zoho\Controllers\ZohoSync;
use App\Plugins\Zoho\Integrations\Crm\Facades\ZohoCrm;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use App\User;
use Exception;
use Illuminate\Http\Request;

class ZohoCrmController extends ZohoBaseController
{
    protected ?Crm $crm = null;

    /**
     * Get the Crm instance lazily to avoid API calls during route registration.
     */
    protected function crm(): Crm
    {
        if (! $this->crm instanceof \App\Plugins\Zoho\Integrations\Crm\Controllers\Crm) {
            $this->crm = new Crm();
        }

        return $this->crm;
    }

    public function syncFields(): \Illuminate\Http\JsonResponse
    {
        try {
            resolve(ZohoSync::class)->sync(
                platform: 'crm',
                module: 'Contacts',
                fields: $this->crm()->fields('Contacts')->toArray()
            );

            resolve(ZohoSync::class)->sync(
                platform: 'crm',
                module: 'Accounts',
                fields: $this->crm()->fields('Accounts')->toArray()
            );

            return successResponse('CRM fields synced successfully');
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
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
    public function updateToZohoCrm(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => ['required', 'email'],
            ]);

            $this->addUserDataToCrm($data['email']);

            return successResponse('CRM contact created successfully');
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function addUserDataToCrm(string $email): void
    {
        $this->insertModuledata('Contacts', $email);

        $this->insertModuledata('Accounts', $email);
    }

    protected function insertModuledata(string $module, string $email): void
    {
        $user = User::where('email', $email)->firstOrFail();

        $zohoFields = ZohoFields::wherePlatform('crm')
            ->whereModule($module)
            ->get();

        $mappings = ZohoFieldMappings::with('faveoLocalField')->get();

        $recordData = zohoMappedFields(
            $zohoFields, // @phpstan-ignore argument.type
            $mappings, // @phpstan-ignore argument.type
            $user
        );

        if ($recordData === []) {
            return;
        }

        ZohoCrm::create($module, $recordData);
    }
}
