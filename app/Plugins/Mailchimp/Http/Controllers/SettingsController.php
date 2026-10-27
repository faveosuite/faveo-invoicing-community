<?php

namespace App\Plugins\Mailchimp\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Model\Common\Mailchimp\MailchimpField;
use App\Model\Common\Mailchimp\MailchimpFieldAgoraRelation;
use App\Model\Common\Mailchimp\MailchimpGroup;
use App\Model\Common\Mailchimp\MailchimpGroupAgoraRelation;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Model\Common\StatusSetting;
use App\Model\Product\Product;
use App\Plugins\Mailchimp\Exceptions\MailchimpApiException;
use App\Plugins\Mailchimp\Http\Client\MailchimpClient;
use App\Plugins\Mailchimp\Http\Requests\UpdateSettingsRequest;
use App\Plugins\Mailchimp\Services\ContactBuilder;
use App\Plugins\Mailchimp\Services\MailchimpService;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Throwable;

class SettingsController extends Controller
{
    /** Faveo user fields that can be mapped to Mailchimp merge fields (see ContactBuilder::mergeFields). */
    private const array USER_FIELDS = ['first_name', 'last_name', 'company', 'mobile', 'address', 'town', 'country', 'state', 'zip', 'active', 'role', 'source'];

    // ── Admin settings page data ──────────────────────────────────────────────

    public function getSettings(): JsonResponse
    {
        try {
            $setting = MailchimpSetting::first();
            $apiKey = (string) ($setting->api_key ?? '');
            $listsData = ['lists' => [], 'total' => 0, 'has_more' => false];
            $account = null;
            $keyHint = null;

            if ($apiKey !== '') {
                // Last 4 of the key itself, without the "-us7" data-center suffix.
                $keyHint = substr(strtok($apiKey, '-') ?: $apiKey, -4);

                try {
                    $service = resolve(MailchimpService::class);
                    $account = $service->accountName();
                    $listsData = $service->getLists(20, 0);
                } catch (Throwable) {
                    // bad key or network — return empty
                }
            }

            return successResponse('', [
                'api_key' => $apiKey,
                'has_key' => $apiKey !== '',
                'key_hint' => $keyHint,
                'data_center' => $apiKey !== '' && str_contains($apiKey, '-') ? substr($apiKey, strrpos($apiKey, '-') + 1) : null,
                'account_name' => $account,
                'list_id' => $setting?->list_id,
                'subscribe_status' => $setting->subscribe_status ?? 'subscribed',
                'lists' => $listsData['lists'],
                'lists_total' => $listsData['total'],
                'lists_has_more' => $listsData['has_more'],
            ]);
        } catch (Throwable $throwable) {
            \Logger::exception($throwable);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    // ── Paginated list loading (for infinite scroll) ──────────────────────────

    public function getPaginatedLists(Request $request): JsonResponse
    {
        try {
            $count = max(1, min(50, (int) $request->input('count', 20)));
            $offset = max(0, (int) $request->input('offset', 0));

            $result = resolve(MailchimpService::class)->getLists($count, $offset);

            return successResponse('', $result);
        } catch (MailchimpApiException $mailchimpApiException) {
            return errorResponse($mailchimpApiException->getMessage());
        }
    }

    // ── Save / validate API key ───────────────────────────────────────────────

    public function saveApiKey(UpdateSettingsRequest $request): JsonResponse
    {
        // Left blank = keep the saved key (the page never receives it).
        $apiKey = trim((string) $request->input('mailchimp_auth_key'));
        $apiKey = $apiKey !== '' ? $apiKey : (string) MailchimpSetting::value('api_key');
        $status = $request->input('status', 0);

        if ($apiKey === '') {
            return errorResponse(__('message.enter_mailchimp_key'), 422);
        }

        $client = new MailchimpClient($apiKey);

        if (! $client->ping()) {
            return errorResponse(__('message.mailchimp_apikey_error'));
        }

        try {
            MailchimpSetting::firstOrNew([])->fill(['api_key' => $apiKey])->save();
            StatusSetting::where('id', 1)->update(['mailchimp_status' => $status]);

            app()->forgetInstance(MailchimpService::class);
            $listsData = resolve(MailchimpService::class)->getLists(20, 0);

            return successResponse(__('message.mailchimp_setting'), [
                'lists' => $listsData['lists'],
                'lists_total' => $listsData['total'],
                'lists_has_more' => $listsData['has_more'],
            ]);
        } catch (Throwable $throwable) {
            \Logger::exception($throwable);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    // ── Save list selection + subscribe status ────────────────────────────────

    public function saveListSettings(Request $request): JsonResponse
    {
        $request->validate(['list_id' => ['required', 'string']]);

        try {
            MailchimpSetting::firstOrNew()->fill([
                'list_id' => $request->input('list_id'),
                'subscribe_status' => $request->input('subscribe_status', 'subscribed'),
            ])->save();

            return successResponse(__('message.mailchimp_setting_successfully_saved'));
        } catch (Throwable $throwable) {
            \Logger::exception($throwable);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    // ── Sync merge fields from Mailchimp → local DB ───────────────────────────

    public function syncMergeFields(): JsonResponse
    {
        try {
            $removed = resolve(MailchimpService::class)->syncMergeFields();

            return successResponse($this->syncMessage($removed), ['fields' => $this->listFields()]);
        } catch (MailchimpApiException $mailchimpApiException) {
            return errorResponse($mailchimpApiException->getMessage());
        }
    }

    // ── Sync interest categories from Mailchimp → local DB ───────────────────

    public function syncInterestGroups(): JsonResponse
    {
        try {
            $service = resolve(MailchimpService::class);
            $removed = $service->syncInterestGroups();

            $listId = MailchimpSetting::value('list_id');
            $groups = MailchimpGroup::where('list_id', $listId)
                ->select('category_name', 'category_option_id', 'category_id')
                ->get();
            $categories = $service->getInterestCategories();

            return successResponse($this->syncMessage($removed), [
                'groups' => $groups,
                'categories' => $categories,
            ]);
        } catch (MailchimpApiException $mailchimpApiException) {
            return errorResponse($mailchimpApiException->getMessage());
        }
    }

    /**
     * Sync result message, naming what had to be un-mapped because the audience no longer has it.
     *
     * @param  list<string>  $removed
     */
    private function syncMessage(array $removed): string
    {
        $message = (string) __('message.updated-successfully');

        if ($removed === []) {
            return $message;
        }

        return (string) __('message.mailchimp_sync_removed', ['message' => $message, 'items' => implode(', ', $removed)]);
    }

    // ── Mapping page data ─────────────────────────────────────────────────────

    public function getMappingData(): JsonResponse
    {
        try {
            $listId = MailchimpSetting::value('list_id');
            $relation = MailchimpFieldAgoraRelation::first();
            $fields = $this->listFields();
            $groups = MailchimpGroup::where('list_id', $listId)
                ->select('category_name', 'category_option_id', 'category_id')->get();
            $products = Product::pluck('name', 'id');
            $groupRelations = MailchimpGroupAgoraRelation::select('agora_product_id', 'mailchimp_group_cat_id')
                ->orderBy('id')->get();
            $status = StatusSetting::select('mailchimp_product_status', 'mailchimp_ispaid_status')->first();
            try {
                $categories = resolve(MailchimpService::class)->getInterestCategories();
            } catch (Throwable) {
                $categories = [];
            }

            return successResponse('', [
                'relation' => $relation,
                'fields' => $fields,
                'groups' => $groups,
                'products' => $products,
                'group_relations' => $groupRelations,
                'status' => $status,
                'categories' => $categories,
            ]);
        } catch (Throwable $throwable) {
            \Logger::exception($throwable);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    // ── Save field mapping (user fields → merge tags) ─────────────────────────

    public function saveFieldMapping(Request $request): JsonResponse
    {
        $fields = $this->listFields();
        $tags = $fields->pluck('tag')->all();

        // Only Faveo user fields are mappable, each to a merge field of the selected audience.
        $mapping = $request->only(self::USER_FIELDS);
        $request->validate(array_fill_keys(self::USER_FIELDS, ['nullable', 'string', Rule::in($tags)]), [
            'in' => __('message.mailchimp_unknown_field'),
        ]);

        $mapped = array_filter($mapping);
        $missing = $fields->where('required', true)->whereNotIn('tag', $mapped)->pluck('name')->all();

        if ($missing !== []) {
            return errorResponse(__('message.mailchimp_required_missing', ['fields' => implode(', ', $missing)]), 422);
        }

        try {
            MailchimpFieldAgoraRelation::firstOrNew()->fill(array_fill_keys(self::USER_FIELDS, null))->fill($mapping)->save();

            return successResponse(__('message.updated-successfully'));
        } catch (Throwable $throwable) {
            \Logger::exception($throwable);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    // ── Save product → interest group mapping ─────────────────────────────────

    public function saveGroupMapping(Request $request): JsonResponse
    {
        $request->validate([
            'row' => ['array'],
            'row.*' => ['array', 'size:2'],
            'row.*.0' => ['required'],
            'row.*.1' => ['required'],
        ]);

        try {
            MailchimpGroupAgoraRelation::where('id', '!=', 0)->delete();

            foreach ((array) $request->input('row', []) as $row) {
                MailchimpGroupAgoraRelation::create([
                    'agora_product_id' => $row[0],
                    'mailchimp_group_cat_id' => $row[1],
                ]);
            }

            return successResponse(__('message.updated-successfully'));
        } catch (Throwable $throwable) {
            \Logger::exception($throwable);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    // ── Save isPaid mapping (Yes/No category) ─────────────────────────────────

    public function saveIsPaidMapping(Request $request): JsonResponse
    {
        $request->validate(['group' => ['required', 'string']]);

        try {
            $categoryId = $request->input('group');

            // Validate that the category has Yes/No options
            $options = resolve(MailchimpService::class)->getInterestGroupOptions($categoryId);
            $names = array_map(fn (array $o) => strtolower((string) $o['name']), $options);

            $hasYes = array_intersect($names, ['yes', 'true']) !== [];
            $hasNo = array_intersect($names, ['no', 'false']) !== [];

            if (! $hasYes || ! $hasNo) {
                return errorResponse(__('message.group_dropdown_values_required'));
            }

            resolve(MailchimpService::class)->mapIsPaidInterests($categoryId);

            return successResponse(__('message.settings_updated_successfully'));
        } catch (MailchimpApiException $mailchimpApiException) {
            return errorResponse($mailchimpApiException->getMessage());
        }
    }

    // ── Disconnect ────────────────────────────────────────────────────────────

    /**
     * Forget the API key and audience and switch Mailchimp off. Field mapping and
     * interest-group settings stay, so reconnecting the same account needs no setup.
     */
    public function disconnect(): JsonResponse
    {
        MailchimpSetting::firstOrNew()->fill(['api_key' => null, 'list_id' => null])->save();
        StatusSetting::where('id', 1)->update(['mailchimp_status' => 0]);
        app()->forgetInstance(MailchimpService::class);

        return successResponse(__('message.mailchimp_disconnected'));
    }

    // ── Send test ─────────────────────────────────────────────────────────────

    /**
     * Subscribe the signed-in admin with the current audience and mapping, so they
     * can see in Mailchimp exactly what gets sent.
     */
    public function sendTest(): JsonResponse
    {
        /** @var User $admin */
        $admin = auth()->user();

        if (! MailchimpSetting::value('list_id')) {
            return errorResponse(__('message.mailchimp_select_list_first'));
        }

        if (resolve(ContactBuilder::class)->mergeFields($admin) === []) {
            return errorResponse(__('message.mailchimp_test_nothing_mapped'));
        }

        try {
            resolve(MailchimpService::class)->subscribe($admin);

            return successResponse(__('message.mailchimp_test_sent', ['email' => $admin->email]));
        } catch (MailchimpApiException $mailchimpApiException) {
            // Admin-only endpoint: show Mailchimp's real reason so the mapping can be fixed.
            return errorResponse(__('message.mailchimp_test_failed', ['reason' => $mailchimpApiException->getMessage()]));
        }
    }

    // ── Toggle product/isPaid status flags ────────────────────────────────────

    public function updateProductStatus(Request $request): JsonResponse
    {
        StatusSetting::where('id', 1)->update(['mailchimp_product_status' => $request->boolean('status')]);

        return successResponse(__('message.updated-successfully'));
    }

    public function updateIsPaidStatus(Request $request): JsonResponse
    {
        StatusSetting::where('id', 1)->update(['mailchimp_ispaid_status' => $request->boolean('status')]);

        return successResponse(__('message.updated-successfully'));
    }

    /**
     * Merge fields of the selected audience, with Mailchimp's required flag.
     *
     * @return Collection<int, array{tag: string, name: string, type: string, required: bool}>
     */
    private function listFields(): Collection
    {
        return MailchimpField::where('list_id', MailchimpSetting::value('list_id'))
            ->get(['tag', 'name', 'type', 'required'])
            ->map(fn (MailchimpField $f): array => [
                'tag' => (string) $f->tag,
                'name' => (string) $f->name,
                'type' => (string) $f->type,
                'required' => (bool) $f->required,
            ])
            ->values();
    }
}
