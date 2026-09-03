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
use DB;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Logger;
use Pipedrive\versions\v1\Api\DealFieldsApi;
use Pipedrive\versions\v1\Api\OrganizationFieldsApi;
use Pipedrive\versions\v1\Api\PersonFieldsApi;
use Pipedrive\versions\v1\ApiException;
use Pipedrive\versions\v1\Configuration as PipedriveConfiguration;
use Pipedrive\versions\v2\Api\DealsApi;
use Pipedrive\versions\v2\Api\OrganizationsApi;
use Pipedrive\versions\v2\Api\PersonsApi;
use Pipedrive\versions\v2\ApiException as ApiExceptionV2;
use Pipedrive\versions\v2\Configuration as PipedriveConfigurationV2;
use Throwable;

class PipedriveController extends Controller
{
    /**
     * Pipedrive custom fields are always referenced by a randomly generated
     * 40-character hex hash (per the v2 API docs for `custom_fields` on
     * Person/Organization/Deal request bodies) — never by a mnemonic name.
     */
    private const string CUSTOM_FIELD_KEY_PATTERN = '/^[0-9a-f]{40}$/i';

    /**
     * Custom-field types whose v2 value must be `{"value": ...}` rather than
     * a bare scalar — verified live against a real Pipedrive account.
     * `monetary` also needs this shape but is handled separately below since
     * it additionally needs a numeric value. `set` (multi-option) needs an
     * array of option IDs instead, but isn't listed here:
     * transformPipedriveData() only ever picks one active option, so
     * multi-select isn't populated at all yet.
     */
    private const array CUSTOM_FIELD_OBJECT_VALUE_TYPES = ['time', 'timerange', 'daterange', 'address'];

    /**
     * Custom-field types that reject a numeric string and require a true
     * JSON number — verified live. Every local field this app maps from
     * (User columns) is a string, so these are skipped rather than sent as
     * "42" when the mapped value isn't actually numeric.
     */
    private const array CUSTOM_FIELD_NUMERIC_TYPES = ['double', 'monetary'];

    // Per-entity shape config for toV2RequestBody() — see that method.
    private const array PERSON_FIELD_MAP = [
        'top_level' => ['name', 'owner_id', 'org_id', 'add_time', 'update_time', 'visible_to', 'label_ids', 'marketing_status'],
        'list_fields' => ['email' => 'emails', 'phone' => 'phones'],
        'id_list_fields' => ['label' => 'label_ids'],
    ];

    private const array ORGANIZATION_FIELD_MAP = [
        'top_level' => ['name', 'owner_id', 'add_time', 'update_time', 'visible_to', 'label_ids'],
        'object_fields' => ['address' => 'address'],
        'id_list_fields' => ['label' => 'label_ids'],
    ];

    private const array DEAL_FIELD_MAP = [
        'top_level' => ['title', 'owner_id', 'person_id', 'org_id', 'pipeline_id', 'stage_id', 'value', 'currency', 'is_deleted', 'is_archived', 'archive_time', 'status', 'probability', 'lost_reason', 'visible_to', 'close_time', 'won_time', 'lost_time', 'expected_close_date', 'label_ids'],
        'id_list_fields' => ['label' => 'label_ids'],
    ];

    /**
     * @var array<mixed>
     */
    protected array $apiClients = [];

    /**
     * @var array<mixed>
     */
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

        $config = new PipedriveConfiguration;
        $config->setApiKey('x-api-token', $token);

        $configV2 = new PipedriveConfigurationV2;
        $configV2->setApiKey('x-api-token', $token);

        $this->client = new Client;

        // Initialize API clients
        $this->apiClients = [
            'dealField' => new DealFieldsApi($this->client, $config),
            'personField' => new PersonFieldsApi($this->client, $config),
            'persons' => new PersonsApi($this->client, $configV2),
            'organizations' => new OrganizationsApi($this->client, $configV2),
            'organizationFields' => new OrganizationFieldsApi($this->client, $config),
            'deals' => new DealsApi($this->client, $configV2),
        ];

        $this->groups = $this->getGroups();
    }

    /**
     * Get Pipedrive group IDs.
     *
     * @return array<mixed>
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
     *
     * @return array<mixed>
     */
    private function fetchApiData(string $apiClient, string $method, mixed ...$args): array
    {
        try {
            $result = $this->apiClients[$apiClient]->$method(...$args)->getRawData();

            return is_array($result) ? $result : (array) $result;
        } catch (ApiException|ApiExceptionV2 $e) {
            throw new Exception((string) (json_decode((string) $e->getResponseBody())->error ?? ''), $e->getCode(), $e); // @phpstan-ignore cast.string
        } catch (Exception $e) {
            Logger::exception($e);

            return [];
        }
    }

    /**
     * Generic method to perform API actions with error handling.
     */
    private function performApiAction(string $apiClient, string $method, mixed ...$args): mixed
    {
        try {
            $response = $this->apiClients[$apiClient]->$method(...$args);

            if (is_object($response) && method_exists($response, 'getRawData')) {
                $rawData = (array) $response->getRawData();

                return $rawData['id'] ?? $response;
            }

            return $response;
        } catch (ApiException|ApiExceptionV2 $e) {
            return json_decode((string) $e->getResponseBody()); // @phpstan-ignore cast.string
        } catch (Exception $e) {
            Logger::exception($e);

            return null;
        }
    }

    /**
     * Reshape a flat field_key => value map (v1 style) into the structure
     * Pipedrive's v2 API expects, per one of the *_FIELD_MAP constants above:
     *  - `top_level` keys pass through as-is;
     *  - `list_fields`/`object_fields`/`id_list_fields` keys are rewrapped
     *    into the array/object/id-list shape v2 requires (e.g. email -> emails);
     *  - a genuine custom-field hash goes under `custom_fields`;
     *  - anything else is a v1-only field with no v2 representation (e.g.
     *    `first_name`, `industry`, `channel`) and is dropped — Pipedrive
     *    rejects `custom_fields` entries that aren't a real hash, so smuggling
     *    it in there just trades one validation error for another.
     *
     * @param  array<string, mixed>  $data
     * @param  array{top_level?: array<int, string>, list_fields?: array<string, string>, object_fields?: array<string, string>, id_list_fields?: array<string, string>}  $fieldMap  one of the *_FIELD_MAP constants
     * @return array<string, mixed>
     */
    private function toV2RequestBody(array $data, array $fieldMap): array
    {
        $topLevelKeys = $fieldMap['top_level'] ?? [];
        $listFields = $fieldMap['list_fields'] ?? [];
        $objectFields = $fieldMap['object_fields'] ?? [];
        $idListFields = $fieldMap['id_list_fields'] ?? [];

        $payload = [];
        $customFields = [];

        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (isset($listFields[$key])) {
                $payload[$listFields[$key]] = [['value' => $value]];
            } elseif (isset($objectFields[$key])) {
                $payload[$objectFields[$key]] = ['value' => $value];
            } elseif (isset($idListFields[$key])) {
                $payload[$idListFields[$key]] = [(int) $value];
            } elseif (in_array($key, $topLevelKeys, true)) {
                $payload[$key] = $value;
            } elseif (preg_match(self::CUSTOM_FIELD_KEY_PATTERN, (string) $key)) {
                $customFields[$key] = $value;
            }
            // else: v1-only field with no v2 equivalent — dropped, see above.
        }

        if ($customFields !== []) {
            $payload['custom_fields'] = $customFields;
        }

        return $payload;
    }

    /**
     * Retrieve person fields from Pipedrive.
     *
     * @return array<mixed>
     */
    public function getPipedriveFields(): array
    {
        return $this->fetchApiData('personField', 'getPersonFields');
    }

    /**
     * Retrieve organization fields from Pipedrive.
     *
     * @return array<mixed>
     */
    public function getOrganizationFields(): array
    {
        return $this->fetchApiData('organizationFields', 'getOrganizationFields');
    }

    /**
     * Retrieve deal fields from Pipedrive.
     *
     * @return array<mixed>
     */
    public function getDealFields(): array
    {
        return $this->fetchApiData('dealField', 'getDealFields');
    }

    /**
     * Delete a person from Pipedrive.
     */
    public function deletePerson(int $personID): mixed
    {
        return $this->performApiAction('persons', 'deletePerson', $personID);
    }

    /**
     * Delete an organization from Pipedrive.
     */
    public function deleteOrganization(int $organizationId): mixed
    {
        return $this->performApiAction('organizations', 'deleteOrganization', $organizationId);
    }

    /**
     * Delete a deal from Pipedrive.
     */
    public function deleteDeal(int $dealID): mixed
    {
        return $this->performApiAction('deals', 'deleteDeal', $dealID);
    }

    /**
     * Add a person to Pipedrive.
     *
     * @param  array<string, mixed>  $person  flat field_key => value map, e.g. from transformPipedriveData()
     */
    public function addPerson(array $person): mixed
    {
        return $this->performApiAction('persons', 'addPerson', $this->toV2RequestBody($person, self::PERSON_FIELD_MAP));
    }

    /**
     * Add organization to Pipedrive or get existing one.
     *
     * @param  array<mixed>  $organization
     */
    public function addOrGetOrganization(array $organization): mixed
    {
        try {
            if (! isset($organization['name'])) {
                return (object) [
                    'success' => false,
                    'error' => __('message.organization_name_required'),
                ];
            }

            // Search for existing organization
            $orgSearchResult = $this->fetchApiData('organizations', 'searchOrganization', $organization['name'], 'name');
            $orgSearchResult = json_decode((string) json_encode($orgSearchResult), associative: true);
            $orgId = $orgSearchResult['items'][0]['item']['id'] ?? null;

            // Create new organization if not found
            if (! $orgId) {
                $payload = $this->toV2RequestBody($organization, self::ORGANIZATION_FIELD_MAP);
                $orgResponse = $this->fetchApiData('organizations', 'addOrganization', $payload);
                $orgId = $orgResponse['id'] ?? null;
            }

            return $orgId;
        } catch (Exception $exception) {
            Logger::exception($exception);

            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Add a deal to Pipedrive.
     *
     * @param  array<string, mixed>  $deal  flat field_key => value map, e.g. from transformPipedriveData()
     */
    public function addDeal(array $deal): mixed
    {
        return $this->performApiAction('deals', 'addDeal', $this->toV2RequestBody($deal, self::DEAL_FIELD_MAP));
    }

    /**
     * Sync all Pipedrive fields.
     */
    public function syncFields(): JsonResponse
    {
        $this->syncFieldGroup($this->getPipedriveFields(), $this->groups['personId']);
        $this->syncFieldGroup($this->getOrganizationFields(), $this->groups['organizationId']);
        $this->syncFieldGroup($this->getDealFields(), $this->groups['dealId']);

        return successResponse(__('message.pipedrive_fields_synced'));
    }

    /**
     * Sync fields for a specific group.
     *
     * @param  array<mixed>  $fields
     */
    private function syncFieldGroup(array $fields, int $groupId): void
    {
        $existingFields = PipedriveField::where('pipedrive_group_id', $groupId)->get()->keyBy('field_key');

        // Filter bulk-edit-allowed fields
        $allowedFields = collect($fields)->filter(fn ($field): bool => isset($field->bulk_edit_allowed) && $field->bulk_edit_allowed === true &&
            (! isset($field->use_field) || $field->use_field === 'id') &&
            ! in_array($field->key, $this->excludeKeysFromPipedrive($groupId))); // @phpstan-ignore property.notFound

        $newFieldKeys = $allowedFields->pluck('key')->toArray();
        $existingFields->keys()->toArray();

        // Delete fields
        $fieldsToDelete = $existingFields->filter(fn ($field, $key): bool => ! in_array($key, $newFieldKeys));
        if ($fieldsToDelete->isNotEmpty()) {
            PipedriveField::whereIn('id', $fieldsToDelete->pluck('id'))->delete();
        }

        foreach ($allowedFields as $field) {
            /** @var object $field */
            $fieldData = [
                'field_name' => $field->name, // @phpstan-ignore property.notFound
                'field_type' => $field->field_type, // @phpstan-ignore property.notFound
                'pipedrive_group_id' => $groupId,
            ];

            $pipedriveField = PipedriveField::updateOrCreate(
                ['field_key' => $field->key, 'pipedrive_group_id' => $groupId], // @phpstan-ignore property.notFound
                $fieldData
            );

            // Sync field options
            if (isset($field->options)) {
                $newOptions = collect((array) $field->options)->keyBy('id');

                // Get existing options
                $existingOptions = PipedriveFieldOption::where('pipedrive_field_id', $pipedriveField->id)->get()->keyBy('key');

                $newOptionKeys = $newOptions->keys()->all();

                // Delete options
                $optionsToDelete = $existingOptions->filter(fn ($opt, $key): bool => ! in_array($key, $newOptionKeys));
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

    /**
     * @return array<mixed>
     */
    private function excludeKeysFromPipedrive(int $groupID): array
    {
        // org_id/person_id are always set programmatically by addUserToPipedrive()
        // (the newly created org/person's real Pipedrive ID) — never legitimately
        // mappable to a local text field, so keep them out of the admin UI.
        return match ($groupID) {
            $this->groups['personId'] => ['label_ids', 'org_id'],
            $this->groups['organizationId'] => ['label_ids', 'website', 'linkedin'],
            $this->groups['dealId'] => ['user_id', 'org_id', 'person_id'],
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
        } catch (Exception $exception) {
            Logger::exception($exception);
        }
    }

    /**
     * Get local fields for a group.
     */
    public function getLocalFields(int $group_id): JsonResponse
    {
        $pipedriveFields = PipedriveField::with('pipedriveOptions')
            ->where('pipedrive_group_id', $group_id)
            ->get();

        return successResponse(__('message.local_fields_retrieved'), [
            'local_fields' => PipedriveLocalFields::get(),
            'pipedrive_fields' => $pipedriveFields,
        ]);
    }

    /**
     * Map fields between Pipedrive and local system.
     */
    public function mappingFields(Request $request): JsonResponse
    {
        $groupID = $request->input('group_id');
        $group_name = PipedriveGroups::where('id', $groupID)->value('group_name');

        $select1 = $request->input('select1', []);
        $select2 = $request->input('select2', []);

        // select1 is normally [{id: 1}, {id: 2}, ...] but a plain [1, 2, ...]
        // is accepted too. Flatten to plain field ids once, up front, so
        // every whereIn() below compares against real scalars instead of
        // nested arrays (whereIn has no equivalent of where()'s
        // flattenValue() safety net — passing it the raw rows silently
        // matches nothing, which previously reset every other field's
        // mapping to null).
        $selectedFieldIds = collect((array) $select1)
            ->map(fn ($row) => is_array($row) ? ($row['id'] ?? null) : $row)
            ->filter(fn ($id): bool => $id !== null)
            ->all();

        // Validate title field for deals
        if ($group_name === 'Deal' && ! PipedriveField::whereIn('id', $selectedFieldIds)
            ->where('field_key', 'title')
            ->exists()) {
            return errorResponse(__('message.title_field_deals'));
        }

        try {
            DB::transaction(function () use ($select1, $select2, $selectedFieldIds, $groupID): void {
                // Update selected fields
                foreach ($select1 as $key => $row) {
                    $fieldId = is_array($row) ? $row['id'] : $row;
                    $localField = $select2[$key];

                    if ($localField['faveo_fields'] === 'true') {
                        PipedriveField::where('id', $fieldId)->update([
                            'local_field_id' => $localField['id'],
                        ]);
                    } else {
                        // 'id' is a single option id (enum/label) or an array
                        // of option ids (a 'set' multi-option field).
                        PipedriveFieldOption::whereIn('id', (array) $localField['id'])->update([
                            'status' => 1,
                        ]);
                    }
                }

                // Reset non-selected fields
                PipedriveField::where('pipedrive_group_id', $groupID)
                    ->whereNotIn('id', $selectedFieldIds)
                    ->update(['local_field_id' => null]);

                // Reset non-selected options
                $fieldIds = PipedriveField::where('pipedrive_group_id', $groupID)->pluck('id')->toArray();
                $selectedOptionIds = collect((array) $select2)
                    ->filter(fn ($item): bool => isset($item['id']) && $item['faveo_fields'] !== 'true')
                    ->flatMap(fn ($item): array => (array) $item['id'])
                    ->all();

                PipedriveFieldOption::whereIn('pipedrive_field_id', $fieldIds)
                    ->whereNotIn('id', $selectedOptionIds)
                    ->update(['status' => 0]);

                // Run mapping test **within** the transaction
                $response = resolve(self::class)->testPipedriveMapping($groupID);
                if ($response !== true) {
                    // Throwing exception will trigger rollback
                    throw new Exception((string) $response);
                }
            });
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }

        return successResponse(__('message.fields_mapped_successfully'));
    }

    /**
     * Test Pipedrive mapping with a temporary user.
     */
    private function testPipedriveMapping(int $groupId): bool|string
    {
        $user = User::factory()->create([
            'user_name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@gamil.com',
            'mobile' => '1234567890',
            'company' => 'Test Company',
            'address' => 'Test Address',
            'town' => 'Test Town',
        ]);

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
                    $this->groups['personId'] => $this->deletePerson((int) $response),
                    $this->groups['organizationId'] => $this->deleteOrganization((int) $response),
                    $this->groups['dealId'] => $this->deleteDeal((int) $response),
                    default => null,
                };

                return true;
            }

            // performApiAction() returns the decoded error body on failure
            if (isset($response->success) && $response->success === false) {
                return $response->error; // @phpstan-ignore property.notFound
            }

            return true;
        } catch (Exception $exception) {
            return $exception->getMessage();
        } finally {
            $user->forceDelete();
        }
    }

    /**
     * Get field mapping for a group.
     */
    public function getMapFields(int $group_id): JsonResponse
    {
        try {
            $group_name = PipedriveGroups::where('id', $group_id)->value('group_name');

            if (! $group_name) {
                return errorResponse('Invalid group ID provided.');
            }

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
                    $selectedField = [];

                    if ($field->local_field_id !== null) {
                        $matchedLocal = $localFields->firstWhere('id', $field->local_field_id);
                        if ($matchedLocal) {
                            $selectedField = [
                                'id' => $matchedLocal->id,
                                'value' => $matchedLocal->field_name,
                            ];
                        }
                    }

                    if ($selectedField === [] && $field->pipedriveOptions->isNotEmpty()) {
                        $activeOptions = $field->pipedriveOptions->where('status', 1);

                        if ($field->field_type === 'set') {
                            // Multi-option field — every active option, not just one.
                            $selectedField = $activeOptions->values()
                                ->map(fn ($option): array => ['id' => $option->id, 'value' => $option->value])
                                ->all();
                        } elseif ($activeOption = $activeOptions->first()) {
                            $selectedField = [
                                'id' => $activeOption->id,
                                'value' => $activeOption->value,
                            ];
                        }
                    }

                    $field->selected_field = $selectedField; // @phpstan-ignore property.notFound
                    $field->local_field_options = $localFieldsArray; // @phpstan-ignore property.notFound

                    return $field;
                });

            $data = [
                'group_id' => $group_id,
                'title' => $title,
                'groups' => $groups,
                'pipedriveData' => [
                    'local_fields' => $localFieldsArray,
                    'pipedrive_fields' => $pipedriveFields,
                ],
            ];

            return successResponse(__('message.pipedrive_fetched_successfully'), $data);
        } catch (Throwable) {
            return errorResponse(__('message.unable_to_fetch_pipedrive_data'));
        }
    }

    /**
     * Get dropdown options for a field.
     */
    public function getDropdown(Request $request): JsonResponse
    {
        $id = $request->input('pipedrive_field_id');

        $fieldOptions = PipedriveFieldOption::where('pipedrive_field_id', $id)
            ->get(['id', 'value']);

        if ($fieldOptions->isEmpty()) {
            $localOptions = PipedriveLocalFields::get(['id', 'field_name'])->map(fn ($item): array => [
                'id' => $item->id,
                'value' => $item->field_name,
            ]);

            return successResponse('', [
                'is_faveo_options' => true,
                'is_multi' => false,
                'options' => $localOptions,
            ]);
        }

        return successResponse('', [
            'is_faveo_options' => false,
            'is_multi' => PipedriveField::where('id', $id)->value('field_type') === 'set',
            'options' => $fieldOptions,
        ]);
    }

    /**
     * Transform user data for Pipedrive.
     *
     * @param  array<mixed>  $customFields
     * @return array<mixed>
     */
    private function transformPipedriveData(User $user, int $groupId, array $customFields = []): array
    {
        // Get all mapped fields for the group
        $pipedriveFields = PipedriveField::where('pipedrive_group_id', $groupId)
            ->with([
                'localField',
                'pipedriveOptions' => function ($q): void {
                    $q->where('status', 1);
                },
            ])
            ->get();

        // Map fields to values
        $mapped = $pipedriveFields->mapWithKeys(function ($field) use ($user): array {
            $result = [];
            $fieldKey = $field->field_key;
            $localFieldKey = $field->localField->field_key ?? null;

            // Use local field mapping if available
            if ($localFieldKey && ! empty($user->{$localFieldKey})) {
                $value = $this->userTransform($user, $localFieldKey);

                // Custom fields (real 40-char hash keys — built-in fields are
                // shaped by toV2RequestBody() instead) of some types need
                // their value reshaped for v2, verified live per field type.
                $isCustomField = preg_match(self::CUSTOM_FIELD_KEY_PATTERN, (string) $fieldKey) === 1;

                if ($isCustomField && in_array($field->field_type, self::CUSTOM_FIELD_NUMERIC_TYPES, true)) {
                    // double/monetary reject a numeric string outright — skip
                    // if the mapped value isn't actually numeric.
                    if (is_numeric($value)) {
                        $result[$fieldKey] = $field->field_type === 'monetary' ? ['value' => (float) $value] : (float) $value;
                    }
                } elseif ($isCustomField && in_array($field->field_type, self::CUSTOM_FIELD_OBJECT_VALUE_TYPES, true)) {
                    $result[$fieldKey] = ['value' => $value];
                } else {
                    $result[$fieldKey] = $value;
                }
            }
            // Otherwise use the active option(s) if available — Pipedrive
            // option IDs are always numeric; cast so the JSON body sends a
            // number, not a string (v2 rejects a string here). A 'set' field
            // takes every active option as an array; anything else takes one.
            elseif ($field->pipedriveOptions->isNotEmpty()) {
                if ($field->field_type === 'set') {
                    $result[$fieldKey] = $field->pipedriveOptions->map(fn ($option): int => (int) $option->key)->values()->all();
                } else {
                    $result[$fieldKey] = (int) $field->pipedriveOptions->first()->key;
                }
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
            'country' => Country::where('country_code_char2', $user->country)->value('country_name'),
            default => $user->{$userField},
        };
    }
}
