<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\Country;
use App\Model\Common\PipedriveField;
use App\Model\Common\PipedriveGroups;
use App\Model\Common\PipedriveLocalFields;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Pipedrive\versions\v1\Api;
use Pipedrive\versions\v1\Configuration as PipedriveConfiguration;

class PipedriveController extends Controller
{
    protected Api\DealFieldsApi $dealFieldApi;
    protected Api\PersonFieldsApi $personFieldApi;
    protected Api\PersonsApi $personsApi;
    protected Api\OrganizationFieldsApi $organizationFieldsApi;
    protected Api\OrganizationsApi $organizationsApi;
    protected Api\DealsApi $dealsApi;

    protected Client $client;

    /**
     * Initialize Pipedrive API clients.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $token = ApiKey::value('pipedrive_api_key');

        $config = new PipedriveConfiguration();
        $config->setApiKey('api_token', $token);

        $this->client = new Client();

        $this->dealFieldApi = new Api\DealFieldsApi($this->client, $config);
        $this->personFieldApi = new Api\PersonFieldsApi($this->client, $config);
        $this->personsApi = new Api\PersonsApi($this->client, $config);
        $this->organizationsApi = new Api\OrganizationsApi($this->client, $config);
        $this->organizationFieldsApi = new Api\OrganizationFieldsApi($this->client, $config);
        $this->dealsApi = new Api\DealsApi($this->client, $config);
    }

    /**
     * Retrieve person fields from Pipedrive.
     */
    public function getPipedriveFields(): array
    {
        try {
            return $this->personFieldApi->getPersonFields()->getRawData();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getOrganizationFields()
    {
        try {
            return $this->organizationFieldsApi->getOrganizationFields()->getRawData();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getDealFields()
    {
        try {
            return $this->dealFieldApi->getDealFields()->getRawData();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function addPerson($person)
    {
        try {
            $response = (array) $this->personsApi->addPerson($person)->getRawData();
            $personId = isset($response['id']) ? $response['id'] : null;

            return $personId;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function addOrGetOrganization($organization)
    {
        try {
            $orgSearchResult = (array) $this->organizationsApi->searchOrganization($organization['name'], 'name')->getRawData();
            $orgId = isset($orgSearchResult['items'][0]['item']['id']) ? $orgSearchResult['items'][0]['item']['id'] : null;

            if (! $orgId) {
                $orgResponse = (array) $this->organizationsApi->addOrganization($organization)->getRawData();
                $orgId = isset($orgResponse['id']) ? $orgResponse['id'] : null;
            }

            return $orgId;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function addDeal($deal)
    {
        try {
            $response = (array) $this->dealsApi->addDeal($deal)->getRawData();

            return isset($response['id']) ? $response['id'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getGroups()
    {
        $personId = PipedriveGroups::where('group_name', 'Person')->value('id');
        $organizationId = PipedriveGroups::where('group_name', 'Organization')->value('id');
        $dealId = PipedriveGroups::where('group_name', 'Deal')->value('id');

        return ['personId' => $personId, 'organizationId' => $organizationId, 'dealId' => $dealId];
    }

    public function syncFields()
    {
        $groups = $this->getGroups();
        $this->syncFieldGroup($this->getPipedriveFields(), $groups['personId']); // Person
        $this->syncFieldGroup($this->getOrganizationFields(), $groups['organizationId']); // Organization
        $this->syncFieldGroup($this->getDealFields(), $groups['dealId']); // Deal

        return successResponse('Pipedrive fields synced successfully');
    }

    private function syncFieldGroup(array $fields, int $groupId): void
    {
        // Get all the current fields for the given group
        $existingFields = PipedriveField::where('pipedrive_group_id', $groupId)->get();

        // Extract the field keys from the incoming data
        $newFieldKeys = collect($fields)->pluck('key')->toArray();

        // Delete fields that are no longer in the incoming fields list
        $fieldsToDelete = $existingFields->filter(function ($field) use ($newFieldKeys) {
            return ! in_array($field->field_key, $newFieldKeys);
        });

        // Remove the obsolete fields
        PipedriveField::whereIn('id', $fieldsToDelete->pluck('id'))->delete();

        // Sync the remaining fields (add or update)
        foreach ($fields as $field) {
            PipedriveField::updateOrCreate(
                [
                    'field_key' => $field->key,
                    'pipedrive_group_id' => $groupId,
                ],
                [
                    'field_name' => $field->name,
                    'field_type' => $field->field_type,
                ]
            );
        }
    }

    /**
     * Create a new Pipedrive person, organization, and deal.
     */
    public function addUserToPipedrive(User $user): void
    {
        try {
            if (! StatusSetting::value('pipedrive_status')) {
                return;
            }

            $groups = $this->getGroups();

            $organization = $this->transformPipedriveData($user, $groups['organizationId']);
            $orgID = $this->addOrGetOrganization($organization);
            $person = $this->transformPipedriveData($user, $groups['personId'], ['org_id' => $orgID]);
            $personID = $this->addPerson($person);
            $data = $this->transformPipedriveData($user, $groups['dealId'], ['org_id' => $orgID, 'person_id' => $personID]);
            $this->addDeal($data);
        } catch (\Exception $e) {
            throw new \Exception('Error adding user to Pipedrive: '.$e->getMessage());
        }
    }

    public function pipedriveSettings()
    {
        $groups = $this->getGroups();
        $apiKey = ApiKey::value('pipedrive_api_key');
        $isVerificationEnabled = ApiKey::value('require_pipedrive_user_verification');
        $settings = Setting::first();

        return view('themes.default1.common.pipedrive.settings', compact('apiKey', 'groups', 'settings', 'isVerificationEnabled'));
    }

    public function getLocalFields($group_id)
    {
        $pipedriveFields = PipedriveField::where('pipedrive_group_id', $group_id)->get();

        return successResponse('Local fields retrieved successfully', [
            'local_fields' => PipedriveLocalFields::get(),
            'pipedrive_fields' => $pipedriveFields,
        ]);
    }

    public function mappingFields(Request $request)
    {
        $groupID = $request->input('group_id');
        $group_name = PipedriveGroups::where('id', $groupID)->value('group_name');
        $data = collect($request->all())->filter();

        $required = $data->filter(function ($value) {
            return $value === 'title';
        })->isEmpty();

        if ($group_name === 'Deal' && $required) {
            return errorResponse('The name field is required for deals.');
        }

        $localFields = PipedriveLocalFields::whereIn('field_key', $data->keys())->pluck('id', 'field_key');

        $data->each(function ($pipedriveKey, $localKey) use ($groupID, $localFields) {
            if (isset($localFields[$localKey])) {
                PipedriveField::updateOrCreate(
                    ['field_key' => $pipedriveKey, 'pipedrive_group_id' => $groupID],
                    ['local_field_id' => $localFields[$localKey]]
                );
            }
        });

        PipedriveField::where('pipedrive_group_id', $groupID)
            ->whereNotIn('field_key', $data->values())->update(['local_field_id' => null]);

        return successResponse('Fields mapped successfully');
    }

    public function getMapFields($group_id)
    {
        $group_name = PipedriveGroups::where('id', $group_id)->value('group_name');

        $title = match ($group_name) {
            'Person' => \Lang::get('message.contact_mapping'),
            'Organization' => \Lang::get('message.organization_mapping'),
            'Deal' => \Lang::get('message.deal_mapping'),
            default => '',
        };

        return view('themes.default1.common.pipedrive.map', compact('group_id', 'title'));
    }

    private function transformPipedriveData(User $user, int $groupId, array $customFields = []): array
    {
        $transformed = PipedriveField::where('pipedrive_group_id', $groupId)
            ->with('localField')
            ->get()
            ->mapWithKeys(function ($field) use ($user) {
                $localFieldKey = $field->localField->field_key ?? null;

                if ($localFieldKey && ! empty($user->{$localFieldKey})) {
                    return [$field->field_key => $this->userTransform($user, $localFieldKey)];
                }

                return [];
            })
            ->toArray();

        return array_merge($transformed, $customFields);
    }

    private function userTransform(User $user, string $userField): mixed
    {
        return match ($userField) {
            'mobile' => '+'.$user->mobile_code.' '.$user->mobile,
            'country' => Country::where('country_code_char2', $user->country)->value('nicename'),
            default => $user->{$userField},
        };
    }

    public function updateVerificationStatus(Request $request)
    {
        $verificationStatus = (bool) $request->input('require_pipedrive_user_verification');
        ApiKey::find(1)->update(['require_pipedrive_user_verification' => $verificationStatus]);

        return successResponse(__('message.pipedrive_verification_updated'));
    }
}
