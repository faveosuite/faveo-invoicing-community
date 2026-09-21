<?php

namespace App\Traits\Order;

use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Product\Subscription;
use App\Services\SubscriptionRenewalService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

trait UpdateDates
{
    public function updateLicenseDetails(Request $request): JsonResponse
    {
        $this->validate($request, [
            'orderid' => 'required',
            'agents' => 'sometimes|integer|min:0|max:9999',
        ]);

        try {
            $service = resolve(SubscriptionRenewalService::class);
            $sub = Subscription::where('order_id', $request->input('orderid'))->firstOrFail();

            $requested = 0;
            $skipped = [];

            if ($request->filled('update_end')) {
                $requested++;
                if (! $service->setDate($sub, 'update_ends_at', $this->parseDate($request->input('update_end')))) {
                    $skipped[] = __('message.updates_expiry');
                }
            }

            if ($request->filled('subscription_end')) {
                $requested++;
                if (! $service->setDate($sub, 'ends_at', $this->parseDate($request->input('subscription_end')))) {
                    $skipped[] = __('message.license_expiry');
                }
            }

            if ($request->filled('support_end')) {
                $requested++;
                if (! $service->setDate($sub, 'support_ends_at', $this->parseDate($request->input('support_end')))) {
                    $skipped[] = __('message.support_expiry');
                }
            }

            if ($request->filled('limit')) {
                $service->updateInstallationLimit($sub, (int) $request->input('limit'));
            }

            // Agents is not a subscription column — it lives in the last four
            // digits of the license, so it gets its own write path below.
            if ($request->filled('agents')) {
                $failure = $this->setAgents((int) $sub->order_id, (int) $request->input('agents'));
                if ($failure !== null) {
                    return errorResponse($failure);
                }
            }

            // Every requested date field was blocked by this product's license
            // type — nothing actually changed, so this must not read as success.
            if ($skipped && count($skipped) === $requested) {
                return errorResponse(__('message.fields_not_permitted', ['fields' => implode(', ', $skipped)]));
            }

            if ($skipped) {
                return successResponse(__('message.some_fields_not_permitted', ['fields' => implode(', ', $skipped)]));
            }

            return successResponse(__('message.updated-successfully'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return errorResponse(__('message.record_not_found'));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    /**
     * Set an order's agent count to a target total, no invoice involved — this
     * is the admin's correction path, as opposed to the client's paid
     * CloudExtraActivities::agentAlteration() flow.
     *
     * @return string|null An error message, or null when the change went through.
     */
    private function setAgents(int $orderId, int $agents): ?string
    {
        /** @var Order $order */
        $order = Order::findOrFail($orderId);
        $license = (string) $order->serial_key;
        $current = (int) substr($license, 12, 16);

        if ($current === $agents) {
            return null;
        }

        $cloud = resolve(CloudExtraActivities::class);

        // Self-hosted installs phone home and stamp installation_path too (see
        // BaseHomeController::...), so a path existing says nothing about there
        // being a cloud tenant behind it — only the product does. Getting this
        // backwards would POST a self-hosted customer's own domain to the cloud
        // server, and ping their install's /api/agent-check.
        $installationPath = null;

        if (in_array((int) $order->product, cloudPopupProducts())) {
            $installationPath = $cloud->installationPathFor($license);

            if (! $installationPath) {
                return __('message.installation_path_not_found');
            }

            // Cutting seats below the agents actually in use would strand them.
            // 0 means unlimited, so it's never a reduction.
            if ($agents > 0 && ($agents < $current || $current === 0)
                && $cloud->checktheAgent($agents, $installationPath)) {
                return __('message.agent_reduce');
            }
        }

        $result = $cloud->doTheAgentAltering((string) $agents, $license, $orderId, $installationPath, (int) $order->product);

        if ($result->getStatusCode() !== 200) {
            return __('message.change_agents_failed');
        }

        // Renewal pricing multiplies the plan price by the invoice item's
        // agents, not by the license, so a stale item would re-bill the old
        // seat count. Two readers, two rows: getInvoiceByOrderId() reads the
        // order's own item, get-renew-cost reads the newest one.
        $order->invoiceItem?->update(['agents' => $agents]);

        InvoiceItem::whereHas('invoice', fn (Builder $q) => $q->whereHas('orders', fn (Builder $q) => $q->where('orders.id', $orderId))) // @phpstan-ignore argument.templateType
            ->orderByDesc('id')
            ->first()?->update(['agents' => $agents]);

        return null;
    }

    private function parseDate(string $date): string
    {
        return Date::createFromFormat('m/d/Y', $date)?->format('Y-m-d H:i:s') ?? ''; // @phpstan-ignore nullsafe.neverNull
    }
}
