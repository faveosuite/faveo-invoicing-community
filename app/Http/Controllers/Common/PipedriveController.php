<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\Country;
use App\Model\Common\PipedriveField;
use App\Model\Common\PipedriveLocalFields;
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
    protected Api\OrganizationsApi $organizationsApi;
    protected Api\DealsApi $dealsApi;

    protected Client $client;

    /**
     * Initialize Pipedrive API clients.
     */
    public function __construct()
    {
        $this->middleware(['auth','admin']);
        $token = ApiKey::value('pipedrive_api_key');

        $config = new PipedriveConfiguration();
        $config->setApiKey('api_token', $token);

        $this->client = new Client();

        $this->dealFieldApi = new Api\DealFieldsApi($this->client, $config);
        $this->personFieldApi = new Api\PersonFieldsApi($this->client, $config);
        $this->personsApi = new Api\PersonsApi($this->client, $config);
        $this->organizationsApi = new Api\OrganizationsApi($this->client, $config);
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

    /**
     * Create a new Pipedrive person, organization, and deal.
     */
    public function addUserToPipedrive(User $user): void
    {
        try {
            if (! StatusSetting::value('pipedrive_status')) {
                return;
            }

            // Check if user already exists in Pipedrive
            $searchResult = $this->personsApi->searchPersons($user->email, 'email')->getRawData();

            if (! empty($searchResult->items)) {
                return;
            }

            // Create Organization if company name is available
            $orgId = null;
            if ($user->company) {
                // Check if the organization already exists
                $orgSearchResult = $this->organizationsApi->searchOrganization($user->company, 'name')->getRawData();
                $orgId = $orgSearchResult['items'][0]['item']['id'] ?? null;

                if (!$orgId) {
                    $orgResponse = $this->organizationsApi->addOrganization(['name' => $user->company]);
                    $orgId = $orgResponse?->getRawData()['id'] ?? null;
                }
            }

            $personData = $this->getPersonDataFromUser($user, $orgId);
            $personResponse = $this->personsApi->addPerson($personData);
            $personId = $personResponse?->getRawData()['id'] ?? null;

            if ($personId && $orgId) {
                $this->dealsApi->addDeal(['title' => $user->company . ' deal', 'person_id' => $personId, 'org_id' => $orgId]);
            }
        } catch (\Exception $e) {
            throw new \Exception('Error adding user to Pipedrive: '.$e->getMessage());
        }
    }

    public function pipedriveSettings()
    {
        return view('themes.default1.common.pipedrive.settings', ['apiKey' => ApiKey::value('pipedrive_api_key')]);
    }

    public function getLocalFields()
    {
        $fieldsFromPipedrive = $this->getPipedriveFields();
        foreach ($fieldsFromPipedrive as $field) {
            PipedriveField::updateOrCreate(
                ['field_key' => $field->key],
                ['field_name' => $field->name, 'field_type' => $field->field_type]
            );
        }

        return successResponse('Local fields retrieved successfully', [
            'local_fields' => PipedriveLocalFields::with('pipedrive')->get(),
            'pipedrive_fields' => PipedriveField::all()
        ]);
    }

    public function mappingFields(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            PipedriveLocalFields::where('field_key', $key)->update(['pipedrive_key' => $value]);
        }
        return successResponse('Fields mapped successfully');
    }

    public function getMapFields()
    {
        return view('themes.default1.common.pipedrive.map');
    }

    protected function getPersonDataFromUser(User $user, ?int $orgId = null): array
    {
        return PipedriveLocalFields::whereNotNull('pipedrive_key')->get()
            ->mapWithKeys(function ($field) use ($user, $orgId) {
                $key = trim($field->pipedrive_key); // Ensure key is not empty or just whitespace
                if (empty($key)) {
                    return []; // Skip empty keys
                }

                return [
                    $key => match ($field->field_key) {
                        'mobile' => $user->mobile ? '+' . $user->mobile_code . ' ' . $user->mobile : null,
                        'country' => Country::where('country_code_char2', $user->country)->value('nicename'),
                        'org_id' => $orgId,
                        default => $user->{$field->field_key} ?? null,
                    }
                ];
            })
            ->toArray();
    }

}
