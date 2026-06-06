<?php

namespace App\Plugins\Mailchimp\Services;

use App\Model\Common\Mailchimp\MailchimpField;
use App\Model\Common\Mailchimp\MailchimpFieldAgoraRelation;
use App\Model\Common\Mailchimp\MailchimpGroup;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Plugins\Mailchimp\Exceptions\MailchimpApiException;
use App\Plugins\Mailchimp\Http\Client\MailchimpClient;
use App\User;

class MailchimpService
{
    private string $listId;

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

    // ── Subscribers ──────────────────────────────────────────────────────────

    /**
     * Subscribe a user. Falls back to PUT if the member already exists
     * so we update instead of failing.
     */
    public function subscribe(User $user): void
    {
        if (! $this->listId) {
            return;
        }

        $mergeFields = $this->contactBuilder->mergeFields($user);

        $payload = [
            'email_address' => $user->email,
            'status' => $this->setting->subscribe_status ?? 'subscribed',
            'merge_fields' => $mergeFields,
        ];

        try {
            $this->client->post("lists/{$this->listId}/members", $payload);
        } catch (MailchimpApiException $e) {
            if ($e->isMemberExists()) {
                // Already subscribed — update instead
                $this->client->put(
                    "lists/{$this->listId}/members/{$this->memberHash($user->email)}",
                    array_merge($payload, ['status_if_new' => $payload['status']])
                );

                return;
            }
            throw $e;
        }
    }

    /**
     * Subscribe a bare email address (e.g. from the newsletter footer widget).
     */
    public function subscribeEmail(string $email): void
    {
        if (! $this->listId) {
            return;
        }

        $payload = [
            'email_address' => $email,
            'status' => $this->setting->subscribe_status ?? 'subscribed',
        ];

        try {
            $this->client->post("lists/{$this->listId}/members", $payload);
        } catch (MailchimpApiException $e) {
            if ($e->isMemberExists()) {
                return;
            }
            throw $e;
        }
    }

    /**
     * Set a member's status to unsubscribed.
     */
    public function unsubscribe(string $email): void
    {
        if (! $this->listId) {
            return;
        }

        try {
            $this->client->patch(
                "lists/{$this->listId}/members/{$this->memberHash($email)}",
                ['status' => 'unsubscribed']
            );
        } catch (MailchimpApiException $e) {
            // 404 = not in list; silently ignore
            if ($e->getHttpStatus() !== 404) {
                throw $e;
            }
        }
    }

    /**
     * Update a subscriber's interest groups after a product purchase.
     */
    public function updatePurchaseInterests(User $user, int $productId, bool $isPaid): void
    {
        if (! $this->listId) {
            return;
        }

        $interests = $this->contactBuilder->purchaseInterests($productId, $isPaid);
        if (empty($interests)) {
            return;
        }

        $this->client->patch(
            "lists/{$this->listId}/members/{$this->memberHash($user->email)}",
            ['interests' => $interests]
        );
    }

    // ── Lists ─────────────────────────────────────────────────────────────────

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

    public function getMergeFields(): array
    {
        if (! $this->listId) {
            return [];
        }
        $result = $this->client->get("lists/{$this->listId}/merge-fields", ['count' => 100]);

        return $result['merge_fields'] ?? [];
    }

    /**
     * Fetch merge fields from Mailchimp and sync them to the local DB.
     *
     * Uses updateOrCreate on merge_id because the unique index on that column
     * is global — Mailchimp reuses the same merge_id numbers across lists,
     * so a plain delete-then-insert can still hit the constraint if a prior
     * record wasn't cleaned up. updateOrCreate is idempotent and safe.
     */
    public function syncMergeFields(): void
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
    }

    // ── Interest Categories ───────────────────────────────────────────────────

    public function getInterestCategories(): array
    {
        if (! $this->listId) {
            return [];
        }
        $result = $this->client->get("lists/{$this->listId}/interest-categories", ['count' => 100]);

        return $result['categories'] ?? [];
    }

    public function getInterestGroupOptions(string $categoryId): array
    {
        if (! $this->listId) {
            return [];
        }
        $result = $this->client->get(
            "lists/{$this->listId}/interest-categories/{$categoryId}/interests",
            ['count' => 100]
        );

        return $result['interests'] ?? [];
    }

    /**
     * Fetch all interest categories + their options and sync to local DB.
     */
    public function syncInterestGroups(): void
    {
        $categories = $this->getInterestCategories();

        foreach ($categories as $category) {
            $options = $this->getInterestGroupOptions($category['id']);

            foreach ($options as $option) {
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
    }

    /**
     * Resolve is_paid_yes / is_paid_no interest IDs from a given category.
     * The category must have interests named "Yes"/"True" and "No"/"False".
     */
    public function mapIsPaidInterests(string $categoryId): void
    {
        $options = $this->getInterestGroupOptions($categoryId);

        foreach ($options as $option) {
            $name = strtolower($option['name']);
            if (in_array($name, ['yes', 'true'], true)) {
                MailchimpFieldAgoraRelation::find(1)?->update(['is_paid_yes' => $option['id']]);
            } elseif (in_array($name, ['no', 'false'], true)) {
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
