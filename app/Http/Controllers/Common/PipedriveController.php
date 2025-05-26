<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\Country;
use App\Model\Common\PipedriveField;
use App\Model\Common\PipedriveFieldOption;
use App\Model\Common\PipedriveGroups;
use App\Model\Common\PipedriveLocalFields;
use App\Model\Common\StatusSetting;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Pipedrive\versions\v1\Api;
use Pipedrive\versions\v1\ApiException;
use Pipedrive\versions\v1\Configuration as PipedriveConfiguration;

class PipedriveController extends Controller
{
    protected array $apiClients = [];
    protected array $groups = [];
    protected Client $client;

    /**
     * Initialize Pipedrive API clients.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);

        if (! StatusSetting::value('pipedrive_status')) {
            abort(404);
        }

        $token = ApiKey::value('pipedrive_api_key');

        $config = new PipedriveConfiguration();
        $config->setApiKey('x-api-token', $token);

        $this->client = new Client();

        // Initialize API clients
        $this->apiClients = [
            'dealField' => new Api\DealFieldsApi($this->client, $config),
            'personField' => new Api\PersonFieldsApi($this->client, $config),
            'persons' => new Api\PersonsApi($this->client, $config),
            'organizations' => new Api\OrganizationsApi($this->client, $config),
            'organizationFields' => new Api\OrganizationFieldsApi($this->client, $config),
            'deals' => new Api\DealsApi($this->client, $config),
        ];

        $this->groups = $this->getGroups();
    }

    /**
     * Get Pipedrive group IDs.
     */
    protected function getGroups(): array
    {
        return [
            'personId' => PipedriveGroups::where('group_name', 'Person')->value('id'),
            'organizationId' => PipedriveGroups::where('group_name', 'Organization')->value('id'),
            'dealId' => PipedriveGroups::where('group_name', 'Deal')->value('id'),
        ];
    }

    /**
     * Generic method to fetch API data with error handling.
     */
    private function fetchApiData(string $apiClient, string $method, ...$args): array
    {
        try {
            $result = $this->apiClients[$apiClient]->$method(...$args)->getRawData();

            return is_array($result) ? $result : (array) $result;
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error('Pipedrive API error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Generic method to perform API actions with error handling.
     */
    private function performApiAction(string $apiClient, string $method, ...$args)
    {
        try {
            $response = $this->apiClients[$apiClient]->$method(...$args);

            if (method_exists($response, 'getRawData')) {
                $rawData = (array) $response->getRawData();

                return $rawData['id'] ?? $response;
            }

            return $response;
        } catch (ApiException $e) {
            return json_decode($e->getResponseBody());
        } catch (\Exception $e) {
            Log::error('Pipedrive action error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Retrieve person fields from Pipedrive.
     */
    public function getPipedriveFields(): array
    {
        return $this->fetchApiData('personField', 'getPersonFields');
    }

    /**
     * Retrieve organization fields from Pipedrive.
     */
    public function getOrganizationFields(): array
    {
        return $this->fetchApiData('organizationFields', 'getOrganizationFields');
    }

    /**
     * Retrieve deal fields from Pipedrive.
     */
    public function getDealFields(): array
    {
        return $this->fetchApiData('dealField', 'getDealFields');
    }

    /**
     * Delete a person from Pipedrive.
     */
    public function deletePerson($personID)
    {
        return $this->performApiAction('persons', 'deletePerson', $personID);
    }

    /**
     * Delete an organization from Pipedrive.
     */
    public function deleteOrganization($organizationId)
    {
        return $this->performApiAction('organizations', 'deleteOrganization', $organizationId);
    }

    /**
     * Delete a deal from Pipedrive.
     */
    public function deleteDeal($dealID)
    {
        return $this->performApiAction('deals', 'deleteDeal', $dealID);
    }

    /**
     * Add a person to Pipedrive.
     */
    public function addPerson($person)
    {
        return $this->performApiAction('persons', 'addPerson', $person);
    }

    /**
     * Add organization to Pipedrive or get existing one.
     */
    public function addOrGetOrganization($organization)
    {
        try {
            if (! isset($organization['name'])) {
                return (object) [
                    'success' => false,
                    'error' => 'Organization name is required',
                ];
            }

            // Search for existing organization
            $orgSearchResult = $this->fetchApiData('organizations', 'searchOrganization', $organization['name'], 'name');
            $orgId = $orgSearchResult['items'][0]['item']['id'] ?? null;

            // Create new organization if not found
            if (! $orgId) {
                $orgResponse = $this->fetchApiData('organizations', 'addOrganization', $organization);
                $orgId = $orgResponse['id'] ?? null;
            }

            return $orgId;
        } catch (ApiException $e) {
            return json_decode($e->getResponseBody());
        } catch (\Exception $e) {
            Log::error('Add/Get organization error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Add a deal to Pipedrive.
     */
    public function addDeal($deal)
    {
        return $this->performApiAction('deals', 'addDeal', $deal);
    }

    /**
     * Sync all Pipedrive fields.
     */
    public function syncFields()
    {
        $this->syncFieldGroup($this->getPipedriveFields(), $this->groups['personId']);
        $this->syncFieldGroup($this->getOrganizationFields(), $this->groups['organizationId']);
        $this->syncFieldGroup($this->getDealFields(), $this->groups['dealId']);

        return successResponse('Pipedrive fields synced successfully');
    }

    /**
     * Sync fields for a specific group.
     */
    private function syncFieldGroup(array $fields, int $groupId): void
    {
        $existingFields = PipedriveField::where('pipedrive_group_id', $groupId)->get()->keyBy('field_key');

        // Filter bulk-edit-allowed fields
        $allowedFields = collect($fields)->filter(function ($field) use ($groupId) {
            return isset($field->bulk_edit_allowed) && $field->bulk_edit_allowed === true &&
                (!isset($field->use_field) || $field->use_field === 'id') &&
                !in_array($field->key, $this->excludeKeysFromPipedrive($groupId));
        });

        $newFieldKeys = $allowedFields->pluck('key')->toArray();
        $existingKeys = $existingFields->keys()->toArray();

        // Delete fields
        $fieldsToDelete = $existingFields->filter(fn($field, $key) => !in_array($key, $newFieldKeys));
        if ($fieldsToDelete->isNotEmpty()) {
            PipedriveField::whereIn('id', $fieldsToDelete->pluck('id'))->delete();
        }

        foreach ($allowedFields as $field) {
            $fieldData = [
                'field_name' => $field->name,
                'field_type' => $field->field_type,
                'pipedrive_group_id' => $groupId,
            ];

            $pipedriveField = PipedriveField::updateOrCreate(
                ['field_key' => $field->key, 'pipedrive_group_id' => $groupId],
                $fieldData
            );

            // Sync field options
            if (isset($field->options)) {
                $newOptions = collect($field->options)->keyBy('id');

                // Get existing options
                $existingOptions = PipedriveFieldOption::where('pipedrive_field_id', $pipedriveField->id)->get()->keyBy('key');

                $newOptionKeys = $newOptions->keys()->toArray();
                $existingOptionKeys = $existingOptions->keys()->toArray();

                // Delete options
                $optionsToDelete = $existingOptions->filter(fn($opt, $key) => !in_array($key, $newOptionKeys));
                if ($optionsToDelete->isNotEmpty()) {
                    PipedriveFieldOption::whereIn('id', $optionsToDelete->pluck('id'))->delete();
                }

                // Create or update options
                foreach ($newOptions as $optionId => $option) {
                    PipedriveFieldOption::updateOrCreate(
                        ['pipedrive_field_id' => $pipedriveField->id, 'key' => $optionId],
                        ['value' => $option->label]
                    );
                }
            }
        }
    }

    private function excludeKeysFromPipedrive($groupID)
    {
        return match ($groupID) {
            $this->groups['personId'] => ['label_ids'],
            $this->groups['organizationId'] => ['label_ids'],
            $this->groups['dealId'] => ['user_id'],
            default => [],
        };
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

            // Create organization first
            $organization = $this->transformPipedriveData($user, $this->groups['organizationId']);
            $orgID = $this->addOrGetOrganization($organization);

            // Create person with org reference
            $person = $this->transformPipedriveData($user, $this->groups['personId'], ['org_id' => $orgID]);
            $personID = $this->addPerson($person);

            // Create deal with both references
            $deal = $this->transformPipedriveData($user, $this->groups['dealId'], [
                'org_id' => $orgID,
                'person_id' => $personID,
            ]);
            $this->addDeal($deal);
        } catch (\Exception $e) {
            throw new \Exception('Error adding user to Pipedrive: '.$e->getMessage());
        }
    }

    /**
     * Get local fields for a group.
     */
    public function getLocalFields($group_id)
    {
        $pipedriveFields = PipedriveField::with('pipedriveOptions')
            ->where('pipedrive_group_id', $group_id)
            ->get();

        return successResponse('Local fields retrieved successfully', [
            'local_fields' => PipedriveLocalFields::get(),
            'pipedrive_fields' => $pipedriveFields,
        ]);
    }

    /**
     * Map fields between Pipedrive and local system.
     */
    public function mappingFields(Request $request)
    {
        $groupID = $request->input('group_id');
        $group_name = PipedriveGroups::where('id', $groupID)->value('group_name');

        $select1 = $request->input('select1', []);
        $select2 = $request->input('select2', []);

        // Validate title field for deals
        if ($group_name === 'Deal' && ! PipedriveField::whereIn('id', $select1)
                ->where('field_key', 'title')
                ->exists()) {
            return errorResponse('The title field is required for deals.');
        }

        try {
            \DB::transaction(function () use ($select1, $select2, $groupID) {
                // Update selected fields
                foreach ($select1 as $key => $fieldId) {
                    $localField = $select2[$key];

                    if ($localField['faveo_fields'] === 'true') {
                        PipedriveField::where('id', $fieldId)->update([
                            'local_field_id' => $localField['id'],
                        ]);
                    } else {
                        PipedriveFieldOption::where('id', $localField['id'])->update([
                            'status' => 1,
                        ]);
                    }
                }

                // Reset non-selected fields
                PipedriveField::where('pipedrive_group_id', $groupID)
                    ->whereNotIn('id', $select1)
                    ->update(['local_field_id' => null]);

                // Reset non-selected options
                $fieldIds = PipedriveField::where('pipedrive_group_id', $groupID)->pluck('id')->toArray();
                $selectedOptionIds = collect($select2)->pluck('id')->toArray();

                PipedriveFieldOption::whereIn('pipedrive_field_id', $fieldIds)
                    ->whereNotIn('id', $selectedOptionIds)
                    ->update(['status' => 0]);

                // Run mapping test **within** the transaction
                $response = app(self::class)->testPipedriveMapping($groupID);
                if ($response !== true) {
                    // Throwing exception will trigger rollback
                    throw new \Exception($response);
                }
            });
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }

        return successResponse('Fields mapped successfully');
    }

    /**
     * Test Pipedrive mapping with a temporary user.
     */
    private function testPipedriveMapping(int $groupId)
    {
        $user = User::factory()->create();

        try {
            $response = match ($groupId) {
                $this->groups['personId'] => $this->addPerson($this->transformPipedriveData($user, $groupId)),
                $this->groups['organizationId'] => $this->addOrGetOrganization($this->transformPipedriveData($user, $groupId)),
                $this->groups['dealId'] => $this->addDeal($this->transformPipedriveData($user, $groupId)),
                default => null,
            };

            // Clean up if successful
            if (is_numeric($response)) {
                match ($groupId) {
                    $this->groups['personId'] => $this->deletePerson($response),
                    $this->groups['organizationId'] => $this->deleteOrganization($response),
                    $this->groups['dealId'] => $this->deleteDeal($response),
                    default => null,
                };

                // Delete the test user
                $user->delete();

                return true;
            } elseif (isset($response->success) && $response->success === false) {
                return $response->error;
            }

            return true;
        } catch (\Exception $e) {
            $user->delete();

            return $e->getMessage();
        }
    }

    /**
     * Get field mapping for a group.
     */
    public function getMapFields($group_id)
    {
        $group_name = PipedriveGroups::where('id', $group_id)->value('group_name');

        $groups = $this->getGroups();

        $title = match ($group_name) {
            'Person' => Lang::get('message.contact_mapping'),
            'Organization' => Lang::get('message.organization_mapping'),
            'Deal' => Lang::get('message.deal_mapping'),
            default => '',
        };

        $localFields = PipedriveLocalFields::get();
        $localFieldsArray = $localFields->toArray();

        $pipedriveFields = PipedriveField::with(['pipedriveOptions', 'localField'])
            ->where('pipedrive_group_id', $group_id)
            ->get()
            ->map(function ($field) use ($localFields, $localFieldsArray) {
                // Determine selected field
                $selectedField = [];

                // Priority 1: Match local_field_id
                if ($field->local_field_id !== null) {
                    $matchedLocal = $localFields->firstWhere('id', $field->local_field_id);
                    if ($matchedLocal) {
                        $selectedField = [
                            'id' => $matchedLocal->id,
                            'value' => $matchedLocal->field_name,
                        ];
                    }
                }

                // Priority 2: Check pipedrive options if no local field match
                if (empty($selectedField) && $field->pipedriveOptions->isNotEmpty()) {
                    $activeOption = $field->pipedriveOptions->firstWhere('status', 1);
                    if ($activeOption) {
                        $selectedField = [
                            'id' => $activeOption->id,
                            'value' => $activeOption->value,
                        ];
                    }
                }

                $field->selected_field = $selectedField;
                $field->local_field_options = $localFieldsArray;

                return $field;
            });

        $pipedriveData = [
            'local_fields' => $localFieldsArray,
            'pipedrive_fields' => $pipedriveFields,
        ];

        return view('themes.default1.common.pipedrive.settings', compact(
            'group_id',
            'title',
            'groups',
            'pipedriveData'
        ));
    }

    /**
     * Get dropdown options for a field.
     */
    public function getDropdown(Request $request)
    {
        $id = $request->input('pipedrive_field_id');

        $fieldOptions = PipedriveFieldOption::where('pipedrive_field_id', $id)
            ->get(['id', 'value']);

        if ($fieldOptions->isEmpty()) {
            $localOptions = PipedriveLocalFields::get(['id', 'field_name'])->map(function ($item) {
                return [
                    'id' => $item->id,
                    'value' => $item->field_name,
                ];
            });

            return successResponse('', [
                'is_faveo_options' => true,
                'options' => $localOptions,
            ]);
        }

        return successResponse('', [
            'is_faveo_options' => false,
            'options' => $fieldOptions,
        ]);
    }

    /**
     * Transform user data for Pipedrive.
     */
    private function transformPipedriveData(User $user, int $groupId, array $customFields = []): array
    {
        // Get all mapped fields for the group
        $pipedriveFields = PipedriveField::where('pipedrive_group_id', $groupId)
            ->with([
                'localField',
                'pipedriveOptions' => function ($q) {
                    $q->where('status', 1);
                },
            ])
            ->get();

        // Map fields to values
        $mapped = $pipedriveFields->mapWithKeys(function ($field) use ($user) {
            $result = [];
            $fieldKey = $field->field_key;
            $localFieldKey = $field->localField->field_key ?? null;

            // Use local field mapping if available
            if ($localFieldKey && ! empty($user->{$localFieldKey})) {
                $result[$fieldKey] = $this->userTransform($user, $localFieldKey);
            }
            // Otherwise use option if available
            elseif ($option = $field->pipedriveOptions?->first()) {
                $result[$fieldKey] = $option->key;
            }

            return $result;
        })->toArray();

        // Add custom fields
        return array_merge($mapped, $customFields);
    }

    /**
     * Transform user field values.
     */
    private function userTransform(User $user, string $userField): mixed
    {
        return match ($userField) {
            'mobile' => '+'.$user->mobile_code.' '.$user->mobile,
            'country' => Country::where('country_code_char2', $user->country)->value('nicename'),
            default => $user->{$userField},
        };
    }
}
