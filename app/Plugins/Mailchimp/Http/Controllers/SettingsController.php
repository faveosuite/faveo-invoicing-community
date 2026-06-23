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
use App\Plugins\Mailchimp\Services\MailchimpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SettingsController extends Controller
{
    // ── Admin settings page data ──────────────────────────────────────────────

    public function getSettings(): JsonResponse
    {
        try {
            $setting = MailchimpSetting::first();
            $apiKey = $setting->api_key ?? '';
            $listsData = ['lists' => [], 'total' => 0, 'has_more' => false];

            if ($apiKey) {
                try {
                    $listsData = resolve(MailchimpService::class)->getLists(20, 0);
                } catch (Throwable) {
                    // bad key or network — return empty
                }
            }

            return successResponse('', [
                'api_key' => $apiKey,
                'list_id' => $setting?->list_id,
                'subscribe_status' => $setting->subscribe_status ?? 'subscribed',
                'lists' => $listsData['lists'],
                'lists_total' => $listsData['total'],
                'lists_has_more' => $listsData['has_more'],
            ]);
        } catch (Throwable $throwable) {
            return errorResponse($throwable->getMessage());
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
        $apiKey = trim((string) $request->input('mailchimp_auth_key'));
        $status = $request->input('status', 0);

        $client = new MailchimpClient($apiKey);

        if (! $client->ping()) {
            return errorResponse(__('message.mailchimp_apikey_error'));
        }

        try {
            MailchimpSetting::firstOrNew(['id' => 1])->fill(['api_key' => $apiKey])->save();
            StatusSetting::where('id', 1)->update(['mailchimp_status' => $status]);

            app()->forgetInstance(MailchimpService::class);
            $listsData = resolve(MailchimpService::class)->getLists(20, 0);

            return successResponse(__('message.mailchimp_setting'), [
                'lists' => $listsData['lists'],
                'lists_total' => $listsData['total'],
                'lists_has_more' => $listsData['has_more'],
            ]);
        } catch (Throwable $throwable) {
            return errorResponse($throwable->getMessage());
        }
    }

    // ── Save list selection + subscribe status ────────────────────────────────

    public function saveListSettings(Request $request): JsonResponse
    {
        $request->validate(['list_id' => ['required', 'string']]);

        try {
            MailchimpSetting::firstOrNew(['id' => 1])->fill([
                'list_id' => $request->input('list_id'),
                'subscribe_status' => $request->input('subscribe_status', 'subscribed'),
            ])->save();

            return successResponse(__('message.mailchimp_setting_successfully_saved'));
        } catch (Throwable $throwable) {
            return errorResponse($throwable->getMessage());
        }
    }

    // ── Sync merge fields from Mailchimp → local DB ───────────────────────────

    public function syncMergeFields(): JsonResponse
    {
        try {
            resolve(MailchimpService::class)->syncMergeFields();
            $fields = MailchimpField::where('list_id', MailchimpSetting::value('list_id'))
                ->pluck('name', 'tag');

            return successResponse(__('message.updated-successfully'), ['fields' => $fields]);
        } catch (MailchimpApiException $mailchimpApiException) {
            return errorResponse($mailchimpApiException->getMessage());
        }
    }

    // ── Sync interest categories from Mailchimp → local DB ───────────────────

    public function syncInterestGroups(): JsonResponse
    {
        try {
            $service = resolve(MailchimpService::class);
            $service->syncInterestGroups();

            $listId = MailchimpSetting::value('list_id');
            $groups = MailchimpGroup::where('list_id', $listId)
                ->select('category_name', 'category_option_id', 'category_id')
                ->get();
            $categories = $service->getInterestCategories();

            return successResponse(__('message.updated-successfully'), [
                'groups' => $groups,
                'categories' => $categories,
            ]);
        } catch (MailchimpApiException $mailchimpApiException) {
            return errorResponse($mailchimpApiException->getMessage());
        }
    }

    // ── Mapping page data ─────────────────────────────────────────────────────

    public function getMappingData(): JsonResponse
    {
        try {
            $listId = MailchimpSetting::value('list_id');
            $relation = MailchimpFieldAgoraRelation::first();
            $fields = MailchimpField::where('list_id', $listId)->pluck('name', 'tag');
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
            return errorResponse($throwable->getMessage());
        }
    }

    // ── Save field mapping (user fields → merge tags) ─────────────────────────

    public function saveFieldMapping(Request $request): JsonResponse
    {
        try {
            MailchimpFieldAgoraRelation::firstOrNew(['id' => 1])->fill($request->all())->save();

            return successResponse(__('message.updated-successfully'));
        } catch (Throwable $throwable) {
            return errorResponse($throwable->getMessage());
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
            return errorResponse($throwable->getMessage());
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

}
