<?php

namespace App\Plugins\Mailchimp\Services;

use App\Model\Common\Country;
use App\Model\Common\Mailchimp\MailchimpFieldAgoraRelation;
use App\Model\Common\Setting;
use App\User;

class ContactBuilder
{
    private ?MailchimpFieldAgoraRelation $relation = null;

    private function relation(): MailchimpFieldAgoraRelation
    {
        return $this->relation ??= MailchimpFieldAgoraRelation::first() ?? new MailchimpFieldAgoraRelation();
    }

    /**
     * Build merge_fields array for a user.
     * Only includes fields that have been mapped in the admin UI.
     */
    public function mergeFields(User $user): array
    {
        $relation = $this->relation();
        $fields = [];

        $country = Country::where('country_code_char2', $user->country)->value('country_name');

        $map = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'company' => $user->company,
            'mobile' => $user->mobile,
            'address' => $user->address,
            'town' => $user->town,
            'country' => $country,
            'state' => $user->state,
            'zip' => $user->zip,
            'active' => $user->active,
            'role' => $user->role,
        ];

        foreach ($map as $field => $value) {
            $tag = $relation->$field ?? null;
            if ($tag) {
                $fields[$tag] = $value;
            }
        }

        // Source — maps to the site title
        if ($relation->source) {
            $fields[$relation->source] = Setting::value('title') ?? '';
        }

        return $fields;
    }

    /**
     * Build interest flags for a purchase event.
     * Returns array of [interestId => bool] ready for the Mailchimp interests payload.
     */
    public function purchaseInterests(int $productId, bool $isPaid): array
    {
        $relation = $this->relation();
        $interests = [];

        $isPaidStatus = \App\Model\Common\StatusSetting::value('mailchimp_ispaid_status');
        $productStatus = \App\Model\Common\StatusSetting::value('mailchimp_product_status');

        if ($isPaidStatus) {
            $idYes = $relation->is_paid_yes;
            $idNo = $relation->is_paid_no;

            if ($idYes) {
                $interests[$idYes] = $isPaid;
            }
            if ($idNo) {
                $interests[$idNo] = ! $isPaid;
            }
        }

        if ($productStatus) {
            $groupId = \App\Model\Common\Mailchimp\MailchimpGroupAgoraRelation::where('agora_product_id', $productId)
                ->value('mailchimp_group_cat_id');

            if ($groupId) {
                $interests[$groupId] = true;
            }
        }

        return $interests;
    }
}
