<?php

namespace App\Plugins\Mailchimp\Services;

use App\Model\Common\Mailchimp\MailchimpField;
use App\Model\Common\Mailchimp\MailchimpFieldAgoraRelation;
use App\Model\Common\Mailchimp\MailchimpGroup;
use App\Model\Common\Mailchimp\MailchimpGroupAgoraRelation;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Model\Product\Product;
use App\Plugins\Mailchimp\Exceptions\MailchimpApiException;
use App\Plugins\Mailchimp\Http\Client\MailchimpClient;
use App\User;

class MailchimpService
{
    /** Mailchimp API path of one audience member: list id, member hash. */
    private const MEMBER_PATH = 'lists/%s/members/%s';

    private readonly string $listId;

    public function __construct(
        private readonly MailchimpClient $client,
        private readonly ContactBuilder $contactBuilder,
        private readonly MailchimpSetting $setting,
    ) {
        $this->listId = $setting->list_id ?? '';
    }

    // ── Auth ─────────────────────────────────────────────────────────────────

    public function ping(): bool
    {
        return $this->client->ping();
    }

    /**
     * The connected Mailchimp account's name, for the settings page.
     */
    public function accountName(): ?string
    {
        return $this->client->get('/', ['fields' => 'account_name'])['account_name'] ?? null;
    }

    // ── Subscribers ──────────────────────────────────────────────────────────

    /**
     * Subscribe a user. Falls back to PUT if the member already exists
     * so we update instead of failing.
     */
    public function subscribe(User $user): void
    {
        if ($this->listId === '' || $this->listId === '0') {
            return;
        }

        $mergeFields = $this->contactBuilder->mergeFields($user);

        $payload = [
            'email_address' => $user->email,
            'status' => $this->setting->subscribe_status ?? 'subscribed',
            'merge_fields' => $mergeFields,
        ];

        try {
            $this->client->post(sprintf('lists/%s/members', $this->listId), $payload);
        } catch (MailchimpApiException $mailchimpApiException) {
            if ($mailchimpApiException->isMemberExists()) {
                // Already subscribed — update instead
                $this->client->put(
                    sprintf(self::MEMBER_PATH, $this->listId, $this->memberHash($user->email)),
                    array_merge($payload, ['status_if_new' => $payload['status']])
                );

                return;
            }

            throw $mailchimpApiException;
        }
    }

    /**
     * Subscribe a bare email address (e.g. from the newsletter footer widget).
     */
    public function subscribeEmail(string $email): void
    {
        if ($this->listId === '' || $this->listId === '0') {
            return;
        }

        $payload = [
            'email_address' => $email,
            'status' => $this->setting->subscribe_status ?? 'subscribed',
        ];

        try {
            $this->client->post(sprintf('lists/%s/members', $this->listId), $payload);
        } catch (MailchimpApiException $mailchimpApiException) {
            if ($mailchimpApiException->isMemberExists()) {
                return;
            }

            throw $mailchimpApiException;
        }
    }

    /**
     * Set a member's status to unsubscribed.
     */
    public function unsubscribe(string $email): void
    {
        if ($this->listId === '' || $this->listId === '0') {
            return;
        }

        try {
            $this->client->patch(
                sprintf(self::MEMBER_PATH, $this->listId, $this->memberHash($email)),
                ['status' => 'unsubscribed']
            );
        } catch (MailchimpApiException $mailchimpApiException) {
            // 404 = not in list; silently ignore
            if ($mailchimpApiException->getHttpStatus() !== 404) {
                throw $mailchimpApiException;
            }
        }
    }

    /**
     * Update a subscriber's interest groups after a product purchase.
     */
    public function updatePurchaseInterests(User $user, int $productId, bool $isPaid): void
    {
        if ($this->listId === '' || $this->listId === '0') {
            return;
        }

        $interests = $this->contactBuilder->purchaseInterests($productId, $isPaid);
        if ($interests === []) {
            return;
        }

        $this->client->patch(
            sprintf(self::MEMBER_PATH, $this->listId, $this->memberHash($user->email)),
            ['interests' => $interests]
        );
    }

    // ── Lists ─────────────────────────────────────────────────────────────────

    /**
     * @return array<mixed>
     */
    public function getLists(int $count = 20, int $offset = 0): array
    {
        $result = $this->client->get('lists', [
            'count' => $count,
            'offset' => $offset,
            'fields' => 'lists.id,lists.name,total_items',
        ]);

        $lists = $result['lists'] ?? [];
        $total = $result['total_items'] ?? count($lists);
        $hasMore = ($offset + $count) < $total;

        return [
            'lists' => $lists,
            'total' => $total,
            'has_more' => $hasMore,
        ];
    }

    // ── Merge Fields ──────────────────────────────────────────────────────────

    /**
     * @return array<mixed>
     */
    public function getMergeFields(): array
    {
        if ($this->listId === '' || $this->listId === '0') {
            return [];
        }

        $result = $this->client->get(sprintf('lists/%s/merge-fields', $this->listId), ['count' => 100]);

        return $result['merge_fields'] ?? [];
    }

    /**
     * Fetch merge fields from Mailchimp and sync them to the local DB.
     *
     * Uses updateOrCreate on merge_id because the unique index on that column
     * is global — Mailchimp reuses the same merge_id numbers across lists,
     * so a plain delete-then-insert can still hit the constraint if a prior
     * record wasn't cleaned up. updateOrCreate is idempotent and safe.
     *
     * Then drops every saved mapping whose merge tag the audience no longer has
     * (e.g. after switching audience), so the mapping page never shows a Faveo
     * field mapped to nothing.
     *
     * @return list<string> Faveo fields whose mapping was removed and must be mapped again.
     */
    public function syncMergeFields(): array
    {
        $fields = $this->getMergeFields();

        foreach ($fields as $field) {
            MailchimpField::updateOrCreate(
                ['merge_id' => $field['merge_id']],
                [
                    'tag' => $field['tag'],
                    'name' => $field['name'],
                    'type' => $field['type'],
                    'required' => $field['required'] ? 1 : 0,
                    'list_id' => $field['list_id'],
                    'options' => json_encode($field['options'] ?? []),
                ]
            );
        }

        $relation = MailchimpFieldAgoraRelation::first();

        // No audience picked = Mailchimp told us nothing; don't read that as "every field is gone".
        if (! $relation instanceof MailchimpFieldAgoraRelation || $this->listId === '' || $this->listId === '0') {
            return [];
        }

        $tags = array_column($fields, 'tag');
        $removed = [];

        // is_paid_* hold interest ids, not merge tags; groups are checked in syncInterestGroups().
        foreach (array_diff($relation->getFillable(), ['is_paid_yes', 'is_paid_no']) as $column) {
            $tag = $relation->getAttribute($column);

            if (filled($tag) && ! in_array($tag, $tags, true)) {
                $relation->setAttribute($column, null);
                $removed[] = ucfirst(str_replace('_', ' ', $column));
            }
        }

        $relation->save();

        return $removed;
    }

    // ── Interest Categories ───────────────────────────────────────────────────

    /**
     * @return array<mixed>
     */
    public function getInterestCategories(): array
    {
        if ($this->listId === '' || $this->listId === '0') {
            return [];
        }

        $result = $this->client->get(sprintf('lists/%s/interest-categories', $this->listId), ['count' => 100]);

        return $result['categories'] ?? [];
    }

    /**
     * @return array<mixed>
     */
    public function getInterestGroupOptions(string $categoryId): array
    {
        if ($this->listId === '' || $this->listId === '0') {
            return [];
        }

        $result = $this->client->get(
            sprintf('lists/%s/interest-categories/%s/interests', $this->listId, $categoryId),
            ['count' => 100]
        );

        return $result['interests'] ?? [];
    }

    /**
     * Fetch all interest categories + their options and sync to local DB, then
     * drop product → group mappings (and the is-paid interests) that point at
     * groups this audience no longer has.
     *
     * @return list<string> Products whose group mapping was removed and must be mapped again.
     */
    public function syncInterestGroups(): array
    {
        $categories = $this->getInterestCategories();
        $interestIds = [];

        foreach ($categories as $category) {
            $options = $this->getInterestGroupOptions($category['id']);

            foreach ($options as $option) {
                $interestIds[] = $option['id'];

                MailchimpGroup::updateOrCreate(
                    ['category_option_id' => $option['id']],
                    [
                        'category_id' => $option['category_id'],
                        'list_id' => $option['list_id'],
                        'category_name' => $option['name'],
                    ]
                );
            }
        }

        // No audience picked = Mailchimp told us nothing; don't read that as "every group is gone".
        if ($this->listId === '' || $this->listId === '0') {
            return [];
        }

        // Options deleted in Mailchimp (or whose group was) go too — otherwise they linger in the pickers.
        MailchimpGroup::where('list_id', $this->listId)->whereNotIn('category_option_id', $interestIds)->delete();

        $stale = MailchimpGroupAgoraRelation::whereNotIn('mailchimp_group_cat_id', $interestIds)->get();
        $removed = array_values(array_unique(Product::whereIn('id', $stale->pluck('agora_product_id'))->get()
            ->map(fn (Product $product): string => $product->name)
            ->all()));
        MailchimpGroupAgoraRelation::whereKey($stale->modelKeys())->delete();

        $relation = MailchimpFieldAgoraRelation::first();

        foreach (['is_paid_yes', 'is_paid_no'] as $column) {
            if ($relation instanceof MailchimpFieldAgoraRelation && filled($relation->getAttribute($column)) && ! in_array($relation->getAttribute($column), $interestIds, true)) {
                $relation->setAttribute($column, null);
            }
        }

        $relation?->save();

        return $removed;
    }

    /**
     * Resolve is_paid_yes / is_paid_no interest IDs from a given category.
     * The category must have interests named "Yes"/"True" and "No"/"False".
     */
    public function mapIsPaidInterests(string $categoryId): void
    {
        $options = $this->getInterestGroupOptions($categoryId);

        foreach ($options as $option) {
            $name = strtolower((string) $option['name']);
            if (in_array($name, ['yes', 'true'], strict: true)) {
                MailchimpFieldAgoraRelation::find(1)?->update(['is_paid_yes' => $option['id']]);
            } elseif (in_array($name, ['no', 'false'], strict: true)) {
                MailchimpFieldAgoraRelation::find(1)?->update(['is_paid_no' => $option['id']]);
            }
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function memberHash(string $email): string
    {
        return md5(strtolower(trim($email)));
    }
}
