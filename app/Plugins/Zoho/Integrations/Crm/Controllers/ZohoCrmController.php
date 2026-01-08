<?php

namespace App\Plugins\Zoho\Integrations\Crm\Controllers;

use App\Plugins\Zoho\Controllers\ZohoBaseController;
use App\Plugins\Zoho\Controllers\ZohoSync;
use App\Plugins\Zoho\Integrations\Crm\Facades\ZohoCrm;
use App\Plugins\Zoho\Models\ZohoFieldMappings;
use App\Plugins\Zoho\Models\ZohoFields;
use App\User;
use Illuminate\Http\Request;

class ZohoCrmController extends ZohoBaseController
{
    protected Crm $crm;

    public function __construct()
    {
        $this->crm = new Crm();
    }

    public function syncFields()
    {
        try {
            app(ZohoSync::class)->sync(
                platform: 'crm',
                module: 'Contacts',
                fields: $this->crm->fields('Contacts')->toArray()
            );

            app(ZohoSync::class)->sync(
                platform: 'crm',
                module: 'Accounts',
                fields: $this->crm->fields('Accounts')->toArray()
            );

            return successResponse('CRM fields synced successfully');
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function getCrmMappedFields($module)
    {
        return $this->getMappedFields('crm', strtolower($module));
    }

    public function getCrmContactsFields()
    {
        return $this->getModulesFields('crm', 'Contacts');
    }

    public function getCrmAccountsFields()
    {
        return $this->getModulesFields('crm', 'Accounts');
    }

    /**
     * Create or update a CRM contact.
     */
    public function updateToZohoCrm(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
            ]);

            $this->addUserDataToCrm($data['email']);

            return successResponse('CRM contact created successfully');
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function addUserDataToCrm(string $email): void
    {
        $this->insertModuledata('Contacts', $email);

        $this->insertModuledata('Accounts', $email);
    }

    protected function insertModuledata(string $module, string $email)
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

        if (! $recordData) {
            return;
        }

        ZohoCrm::create($module, $recordData);
    }
}
