<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\Country;
use App\Model\Common\PipedriveField;
use App\User;
use GuzzleHttp\Client;
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
     * Initialize Pipedrive API clients
     *
     * @throws \Exception If API key is missing
     */
    public function __construct()
    {
        $token = ApiKey::value('pipedrive_api_key');

        if (!$token) {
            throw new \Exception("Pipedrive API key is missing.");
        }

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
    public function getPipedriveFields()
    {
        try {
            return $this->personFieldApi->getPersonFields()->getRawData();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Ensure required fields exist in Pipedrive by checking against the DB mapping.
     */
    public function mapDealField()
    {
        try {
            $fields = $this->getPipedriveFields();
            $requiredFields = PipedriveField::where('active', true)->get();

            foreach ($requiredFields as $field) {
                $existingField = collect($fields)->firstWhere('name', $field->field_name);

                if (!$existingField) {
                    $fieldData = $this->getFieldMappingFromDb($field->field_name);

                    if ($fieldData) {
                        $response = $this->personFieldApi->addPersonField($fieldData);
                        if ($response && $response->getData()->getKey() !== null) {
                            $field->update(['field_key' => $response->getData()->getKey()]);
                        }
                    }
                } else {
                    $field->update(['field_key' => $existingField->key]);
                }
            }

            return successResponse('Fields synchronized successfully');
        } catch (\Exception $e) {
            return errorResponse('Failed to add Pipedrive fields: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve field mapping configuration from the database.
     *
     * @param string $fieldKey
     * @return array|null
     */
    protected function getFieldMappingFromDb(string $fieldKey): ?array
    {
        $mapping = PipedriveField::where('field_name', $fieldKey)->first();

        return $mapping
            ? [
                'name' => $mapping->field_name,
                'field_type' => $mapping->field_type,
            ]
            : null;
    }

    /**
     * Build the person data array using DB-driven field mappings.
     *
     * @param User $user
     * @return array
     */
    protected function getPersonDataFromUser(User $user): array
    {
        $mappings = PipedriveField::where('active', true)->get();
        $data = [];

        foreach ($mappings as $mapping) {
            if (!$mapping->field_key) {
                continue; // Skip mappings without field_key
            }

            switch ($mapping->field_name) {
                case 'Name':
                    $data[$mapping->field_key] = trim("{$user->first_name} {$user->last_name}");
                    break;
                case 'Email':
                    $data[$mapping->field_key] = $user->email;
                    break;
                case 'Phone':
                    if ($user->mobile_code && $user->mobile) {
                        $data[$mapping->field_key] = '+' . $user->mobile_code . ' ' . $user->mobile;
                    }
                    break;
                case 'Country':
                    if ($user->country) {
                        $countryFullName = Country::where('country_code_char2', $user->country)->value('nicename');
                        $data[$mapping->field_key] = $countryFullName;
                    }
                    break;
                default:
                    // Handle custom fields if needed
                    if (isset($user->{$mapping->field_name})) {
                        $data[$mapping->field_key] = $user->{$mapping->field_name};
                    }
            }
        }
        return $data;
    }

    /**
     * Create a new Pipedrive person, organization, and deal.
     *
     * @param User $user
     */
    public function addUserToPipedrive($user)
    {
        try {

            // Check if user already exists in Pipedrive
            $searchResult = $this->personsApi->searchPersons($user->email, 'email')->getRawData();

            if (!empty($searchResult->items)) {
                return null;
            }

            // Create Organization if company name is available
            $orgId = null;
            if ($user->company) {
                $orgResponse = $this->organizationsApi->addOrganization(['name' => $user->company]);
                if ($orgResponse && isset($orgResponse->getRawData()->id)) {
                    $orgId = $orgResponse->getRawData()->id;
                } else {
                    throw new \Exception("Failed to create organization.");
                }
            }

            // Create Person
            $personData = $this->getPersonDataFromUser($user);
            $personResponse = $this->personsApi->addPerson($personData);

            if (!$personResponse || !isset($personResponse->getRawData()->id)) {
                throw new \Exception("Failed to create person.");
            }

            $personId = $personResponse->getRawData()->id;

            // Create Deal if organization was created
            if ($orgId) {
                $dealData = [
                    'title' => $user->company . ' deal',
                    'person_id' => $personId,
                    'org_id' => $orgId,
                ];

                $this->dealsApi->addDeal($dealData);
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception("Error adding user to Pipedrive: " . $e->getMessage());
        }
    }
}
