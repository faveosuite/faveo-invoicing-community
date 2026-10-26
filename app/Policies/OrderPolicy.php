<?php

namespace App\Policies;

use App\Enums\OrderAbility;
use App\Http\Controllers\License\LicensePermissionsController;
use App\Model\Common\Setting;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\Payment\AutoRenewalActivationService;
use App\User;
use Illuminate\Auth\Access\Response;

/**
 * The one place that decides what a customer may do with an order. Order pages
 * show or hide their action buttons from these rules, and the endpoints behind
 * those buttons enforce the same rules — so a hidden button is also a refused request.
 * Call it through Gate with an {@see OrderAbility} case; each case names a method here.
 */
class OrderPolicy
{
    /** Refusal code on adminRenew: the order is renewable, just not right now — show the reason. */
    public const RENEW_BLOCKED = 'renew_blocked';

    public function renew(User $user, Order $order): Response
    {
        $subscription = $order->subscription;

        return match (true) {
            ! $this->owns($user, $order) => Response::denyWithStatus(403, __('message.unauthorized_action')),
            $this->isTerminated($order) => Response::deny(__('message.plan_change_order_terminated')),
            ! $subscription instanceof Subscription || ! $subscription->canRenew() => Response::deny(__('message.no_permission_for_action')),
            (bool) $subscription->is_deleted => Response::deny(__('message.cloud_instance_deleted_no_renew')),
            default => Response::allow(),
        };
    }

    /**
     * Admin renew. A terminated order (renewed on its replacement) or one where nothing
     * expires is simply not renewable; a deleted instance or an unpaid invoice blocks it for
     * now, with {@see RENEW_BLOCKED} so the admin pages show the reason instead of hiding it.
     */
    public function adminRenew(User $user, Order $order): Response
    {
        $subscription = $order->subscription;

        if ($user->role !== 'admin') {
            return Response::denyWithStatus(403, __('message.unauthorized_action'));
        }

        if ($this->isTerminated($order) || ! $subscription instanceof Subscription || ! $subscription->canRenew()) {
            return Response::deny(__('message.no_permission_for_action'));
        }

        $reason = $order->renewBlockedReason();

        return $reason === null ? Response::allow() : Response::deny($reason, self::RENEW_BLOCKED);
    }

    /** Turn on auto-renewal: the renew rule, with a gateway this customer is actually offered. */
    public function enableAutoRenew(User $user, Order $order, string $gateway): Response
    {
        $renew = $this->renew($user, $order);
        if ($renew->denied()) {
            return $renew;
        }

        return in_array($gateway, AutoRenewalActivationService::gatewaysFor((string) $user->country), true)
            ? Response::allow()
            : Response::deny(__('message.no_permission_for_action'));
    }

    /** Downloads for the order as a whole; which releases fall inside them is {@see downloadVersion}. */
    public function download(User $user, Order $order): Response
    {
        $permissions = LicensePermissionsController::permissionsFor($order->productRelation);

        return match (true) {
            ! $this->owns($user, $order) && $user->role !== 'admin' => Response::denyWithStatus(403, __('message.no_permission_for_action')),
            $this->isTerminated($order) => Response::deny(__('message.no_permission_for_action')),
            // Cloud is hosted for the client; the self-hosted build is never theirs to download,
            // whatever the product's download permission says.
            $this->isCloud($order) => Response::deny(__('message.no_permission_for_action')),
            ($permissions['downloadPermission'] ?? 0) != 1 => Response::deny(__('message.no_permission_for_action')),
            ! $order->subscription instanceof Subscription => Response::deny(__('message.no_order_exists_invoice')),
            // "Keep old downloads" off: everything stops once the updates period is over.
            $this->updatesEnded($order) && ! ($permissions['allowDownloadTillExpiry'] ?? 0) => Response::deny(__('message.renew_subscription_download')),
            default => Response::allow(),
        };
    }

    /** One release: only those that came out before the updates period ended. */
    public function downloadVersion(User $user, Order $order, ?string $releasedAt): Response
    {
        $download = $this->download($user, $order);
        if ($download->denied()) {
            return $download;
        }

        $updatesEnd = $this->updatesEnd($order);

        return $updatesEnd === null || ($releasedAt && strtotime($releasedAt) < strtotime($updatesEnd))
            ? Response::allow()
            : Response::deny(__('message.renew_subscription_download'));
    }

    /** Deploy hands out the same files as download, so it never allows more. */
    public function deploy(User $user, Order $order): Response
    {
        if (! Setting::where('id', 1)->value('deployment_enabled')) {
            return Response::deny(__('message.no_permission_for_action'));
        }

        // A plugin isn't a standalone app — it goes on top of an install, so there's nothing to deploy.
        if (strcasecmp(trim((string) $order->productRelation?->licenseType?->name), 'plugin') === 0) {
            return Response::deny(__('message.no_permission_for_action'));
        }

        return $this->download($user, $order);
    }

    public function deleteCloud(User $user, Order $order): Response
    {
        return match (true) {
            ! $this->owns($user, $order) => Response::denyWithStatus(403, __('message.unauthorized_action')),
            ! $this->isCloud($order) => Response::deny(__('message.no_permission_for_action')),
            $this->isTerminated($order) => Response::deny(__('message.no_permission_for_action')),
            (bool) $order->subscription?->is_deleted => Response::deny(__('message.no_permission_for_action')),
            default => Response::allow(),
        };
    }

    /**
     * Reissue the license (frees it for a new domain/server). Never for cloud — the license is
     * bound to the cloud instance, so moving it would cut the instance off. Otherwise admins
     * may for any order; a customer only for their own live order.
     */
    public function reissue(User $user, Order $order): Response
    {
        $admin = $user->role === 'admin';

        return match (true) {
            ! $admin && ! $this->owns($user, $order) => Response::denyWithStatus(403, __('message.reissue_license_invalid_modification_data')),
            $this->isCloud($order), ! $admin && $this->isTerminated($order) => Response::deny(__('message.no_permission_for_action')),
            default => Response::allow(),
        };
    }

    /**
     * The offline license file — download, server binding, add-on files. Only for self-hosted
     * orders set to file mode; the owner (not once terminated) or an admin acting for them.
     */
    public function useLicenseFile(User $user, Order $order): Response
    {
        $admin = $user->role === 'admin';

        return match (true) {
            ! $admin && ! $this->owns($user, $order) => Response::denyWithStatus(403, __('message.access_denied')),
            $order->license_mode !== 'File', $this->isCloud($order) => Response::deny(__('message.no_permission_for_action')),
            ! $admin && $this->isTerminated($order) => Response::deny(__('message.no_permission_for_action')),
            default => Response::allow(),
        };
    }

    /** Base rule for every cloud setting: the customer's own live cloud instance. */
    public function manageCloud(User $user, Order $order): Response
    {
        return match (true) {
            ! $this->owns($user, $order) => Response::denyWithStatus(403, __('message.unauthorized_action')),
            ! $this->isCloud($order) => Response::deny(__('message.no_permission_for_action')),
            $this->isTerminated($order) => Response::deny(__('message.plan_change_order_terminated')),
            ! $order->subscription instanceof Subscription || (bool) $order->subscription->is_deleted => Response::deny(__('message.no_permission_for_action')),
            default => Response::allow(),
        };
    }

    /** Switch to another plan — only one the client's plan list offers ({@see Plan::cloudPlanChangeOptions}). */
    public function changeCloudPlan(User $user, Order $order, int $planId): Response
    {
        $base = $this->changeCloudAgents($user, $order);
        if ($base->denied()) {
            return $base;
        }

        $product = $order->productRelation;

        return $product instanceof Product && Plan::query()->cloudPlanChangeOptions($product)->whereKey($planId)->exists()
            ? Response::allow()
            : Response::deny(__('message.no_permission_for_action'));
    }

    public function changeCloudAgents(User $user, Order $order): Response
    {
        $base = $this->manageCloud($user, $order);
        if ($base->denied()) {
            return $base;
        }

        $pending = $order->pendingCloudChangeInvoice();

        return $pending === null
            ? Response::allow()
            : Response::deny(__('message.order_change_invoice_pending', ['number' => $pending]));
    }

    private function owns(User $user, Order $order): bool
    {
        return (int) $order->client === (int) $user->id;
    }

    private function isCloud(Order $order): bool
    {
        return in_array($order->getRawOriginal('product'), cloudPopupProducts());
    }

    private function isTerminated(Order $order): bool
    {
        return strcasecmp((string) $order->order_status, 'Terminated') === 0;
    }

    /** The updates end date, or null when updates never end. */
    private function updatesEnd(Order $order): ?string
    {
        $end = (string) $order->subscription?->update_ends_at;

        return $end !== '' && $end !== '0000-00-00 00:00:00' ? $end : null;
    }

    private function updatesEnded(Order $order): bool
    {
        $end = $this->updatesEnd($order);

        return $end !== null && strtotime($end) < time();
    }
}
