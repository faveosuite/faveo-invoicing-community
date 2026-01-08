<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Http\Controllers\Auth\BaseAuthController;
use App\Model\Common\StatusSetting;
use App\Model\Order\InvoiceItem;
use App\Model\Product\Product;
use App\Plugins\Zoho\Controllers\ZohoController;
use App\Plugins\Zoho\Integrations\Campaigns\Controllers\ZohoCampaignsController;
use App\Plugins\Zoho\Integrations\Campaigns\Facades\ZohoCampaigns;
use App\User;

class ExternalServiceController extends BaseAuthController
{
    public function addUserToExternalServices($user, string $trigger = 'register'): void
    {
        $status = StatusSetting::first();

        if ($status->pipedrive_status) {
            $this->handleServiceUserSync(
             service: 'pipedrive',
                user: $user,
                status: $status,
                trigger: $trigger,
                callback: fn () => (new PipedriveController())->addUserToPipedrive($user)
            );
        }

        if ($status->zoho_status) {
            $this->handleServiceUserSync(
                service: 'zoho',
                user: $user,
                status: $status,
                trigger: $trigger,
                callback: fn () => (new ZohoController())->addUserToZoho($user)
            );
        }

        if ($status->mailchimp_status) {
            $this->handleServiceUserSync(
                service: 'mailchimp',
                user: $user,
                status: $status,
                trigger: $trigger,
                callback: fn () => $this->addUserToMailchimp($user)
            );
        }
    }

    private function handleServiceUserSync(
        string $service,
               $user,
               $status,
        string $trigger,
        callable $callback
    ): void {

        $requiresVerification = $this->serviceRequireFullVerification($service);

        // Service REQUIRES verification → only allow on FULLY VERIFIED trigger
        if ($requiresVerification) {

            if (!$this->isUserFullyVerified($user, $status)) {
                return;
            }

            $callback();
            return;
        }

        // Service does NOT require verification → only allow on REGISTER
        if ($trigger === 'register') {
            $callback();
        }
    }

    private function isUserFullyVerified(User $user, StatusSetting $settings): bool
    {
        $isEmailVerified = !$settings->emailverification_status || $user->email_verified;
        $isMobileVerified = !$settings->msg91_status || $user->mobile_verified;

        return $isEmailVerified && $isMobileVerified;
    }

    private function serviceRequireFullVerification(string $service): bool
    {
        try {
            return match ($service) {
                'zoho' =>
                (bool) (ApiKey::value('require_zoho_user_verification') ?? false),

                'pipedrive' =>
                (bool) (ApiKey::value('require_pipedrive_user_verification') ?? false),

                'mailchimp' =>
                (bool) (ApiKey::value('require_mailchimp_user_verification') ?? false),

                default => false,
            };
        }
        catch (\Throwable $e) {
            return false;
        }
    }

    public function subscribeForProductsUpdates(int $productId, int $userId, InvoiceItem $item): void
    {
        $status = StatusSetting::select('zoho_status', 'mailchimp_status')->first();

        if (! $status) {
            return;
        }

        if ($status->zoho_status) {
            $this->updateSubscriberForZohoProduct($productId, $userId, $item);
        }

        if ($status->mailchimp_status) {
            $this->updateSubscriberForMailchimpProduct($productId, $userId, $item);
        }
    }

    public function updateSubscriberForMailchimpProduct(int $productId, int $userId, InvoiceItem $item): void
    {
        try {
            $email = User::whereKey($userId)->value('email');

            if (! $email) {
                return;
            }

            $mailchimp = app(MailChimpController::class);

            if ($item->subtotal > 0) {
                $mailchimp->addSubscriberForPaidProduct($email, $productId);
            } else {
                $mailchimp->addSubscriberForFreeProduct($email, $productId);
            }

        } catch (\Throwable $e) {
            \Logger::exception($e);
        }
    }

    public function updateSubscriberForZohoProduct(int $productId, int $userId, InvoiceItem $item): void
    {
        try {
            $email = User::whereKey($userId)->value('email');

            if (! $email) {
                return;
            }

            $productName = Product::whereKey($productId)->value('name');

            $type = $item->subtotal > 0
                ? 'paid_products'
                : 'free_products';

            (new ZohoCampaignsController())->subscribeWithTag($email, $type, $productName ?? $type);

        } catch (\Throwable $e) {
            \Logger::exception($e);
        }
    }


}
