<?php

declare(strict_types=1);

namespace App\Services;

use App\License\Models\License;
use App\License\Services\LicenseService;
use App\Model\Common\FaveoCloud;
use App\Model\Order\Order;
use App\Model\Order\OrderEntitlement;
use App\Model\Payment\Plan;
use App\Model\Product\Grant;
use App\Model\Product\Subscription;
use App\Services\Cloud\CloudApi;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Logger;
use RuntimeException;

/**
 * An order's agent count: what its plan includes plus the Agents it bought (order_entitlements),
 * unless an admin set it with an Agents override.
 * The license code's last four digits carry that count to the product; this is the one place
 * that writes them, and tells a cloud tenant about the change.
 */
class AgentCountService
{
    public function __construct(private readonly LicenseService $licenses, private readonly Client $client)
    {
    }

    /** The order's agents: an admin's Agents override when there is one, else what it pays for ({@see billed()}). Doesn't read the license. */
    public function total(Order $order): int
    {
        return $this->override($order) ?? $this->billed($order);
    }

    /** Plan included + bought Agents; 0 for a flat or unlimited plan, or an order given unlimited agents. */
    public function billed(Order $order): int
    {
        $plan = Plan::find((int) Subscription::where('order_id', $order->id)->value('plan_id'));
        $included = EntitlementService::includedAgents($plan);
        if ($included === null || $this->unlimited($order)) {
            return 0; // flat plan (no Agents row), unlimited plan, or unlimited by override
        }

        return $included + (int) OrderEntitlement::where('order_id', $order->id)
            ->where('entitlement_id', EntitlementService::agents()->id)
            ->value('quantity');
    }

    /**
     * The order has an Agents override of unlimited (an admin's, or the seeder's for a 0000
     * license on a plan with a count): no agents to buy, and a plan change keeps it unlimited.
     */
    public function unlimited(Order $order): bool
    {
        $grant = $this->overrideGrant($order);

        return $grant !== null && $grant->value === null;
    }

    /** The license code ends in 0000: the product runs with unlimited agents, whatever billing counts. */
    public function licenseUnlimited(Order $order): bool
    {
        $code = (string) $order->serial_key;

        return strlen($code) > 4 && substr($code, -4) === '0000';
    }

    /** An admin set the order's agents (a count or unlimited). */
    public function hasOverride(Order $order): bool
    {
        return $this->overrideGrant($order) !== null;
    }

    /** The agent count an admin set on the order; null when none is set or it is unlimited ({@see unlimited()}). */
    public function override(Order $order): ?int
    {
        $value = $this->overrideGrant($order)?->value;

        return $value === null ? null : (int) $value;
    }

    /** The order's Agents override (its value: a count, or null for unlimited). */
    private function overrideGrant(Order $order): ?Grant
    {
        $subscriptionId = Subscription::where('order_id', $order->id)->value('id');
        $licenseIds = License::where('license_order_number', $order->number)->pluck('id');
        if ($subscriptionId === null && $licenseIds->isEmpty()) {
            return null;
        }

        return Grant::where('code', EntitlementService::AGENTS)->where('source', Grant::ADMIN)->active()
            ->where(fn ($q) => $q->when($subscriptionId !== null, fn ($q) => $q->orWhere('subscription_id', $subscriptionId))
                ->when($licenseIds->isNotEmpty(), fn ($q) => $q->orWhereIn('license_id', $licenseIds)))
            ->first();
    }

    /**
     * Writes $total into the license code's last four digits (0 = 0000, unlimited) and the order's
     * serial key. A cloud order's tenant gets the new code too; if it doesn't take it, the license
     * and order are put back and this throws. Call it after commit: the tenant verifies the new
     * code against billing on its own connection.
     */
    public function write(Order $order, int $total): void
    {
        if ($total < 0 || $total > 9999) {
            throw new RuntimeException(sprintf('Agent count %d for order %d does not fit the license code.', $total, $order->id));
        }

        $lastFour = str_pad((string) $total, 4, '0', STR_PAD_LEFT);
        if (substr((string) $order->fresh()?->serial_key, -4) === $lastFour) {
            return; // already carries this count
        }

        $installationPath = null;
        if (in_array((int) $order->product, array_map(intval(...), cloudPopupProducts()), strict: true)) {
            $installationPath = $order->installedDomains()->first();
            if (! $installationPath) {
                // Nothing to push to; the license still carries the count for when an instance checks in.
                Logger::exception(new RuntimeException(sprintf('Order %d has no installed domain; license set to %s but the cloud instance was not told', $order->id, $lastFour)));
            }
        }

        $this->rewrite($order, $lastFour, $installationPath);
    }

    /**
     * The license rewrite and cloud push. $installationPath is the tenant to tell; null = none
     * (self-hosted orders read the code when they next check in).
     */
    private function rewrite(Order $order, string $lastFour, ?string $installationPath): void
    {
        $order = $order->fresh() ?? throw new RuntimeException('Order not found.');
        $oldLicense = (string) $order->serial_key;
        if (substr($oldLicense, -4) === $lastFour) {
            return;
        }
        // Raw ciphertext, so a revert doesn't write the key back in plaintext.
        $oldSerialKey = $order->getRawOriginal('serial_key');

        $licenseCode = substr($oldLicense, 0, -4).$lastFour;
        $this->licenses->updateLicenseCode($oldLicense, $licenseCode);
        Order::whereKey($order->id)->update(['serial_key' => Crypt::encrypt(substr($licenseCode, 0, 12).$lastFour)]);

        if (empty($installationPath)) {
            return;
        }

        try {
            $result = $this->cloudApi()->send('POST', '/performAgentUpgradeOrDowngrade', [
                'licenseCode' => $licenseCode,
                'installation_path' => $installationPath,
                'product_id' => (int) $order->product,
                'old_lic_code' => $oldLicense,
            ]);
            $failure = ($result['status'] ?? null) === 'success' ? null : (json_encode($result) ?: 'unreadable reply');
        } catch (Exception $exception) {
            $failure = $exception->getMessage(); // cloud down, 5xx, unreadable reply
        }

        if ($failure !== null) {
            // The tenant never got the change, so billing must not claim the new count either.
            $this->revert($order, $oldLicense, $licenseCode, $oldSerialKey, $installationPath);

            throw new RuntimeException(sprintf('Cloud agent change to %s failed for order %d, license reverted: %s', $lastFour, $order->id, $failure));
        }
    }

    /**
     * Undo a license rewrite the tenant didn't take, so billing and the tenant keep agreeing on the
     * count (and installedDomains() keeps finding the tenant by its license).
     */
    private function revert(Order $order, string $oldLicense, string $newLicense, ?string $oldSerialKey, string $installationPath): void
    {
        try {
            $this->licenses->updateLicenseCode($newLicense, $oldLicense);
            Order::whereKey($order->id)->update(['serial_key' => $oldSerialKey]);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return;
        }

        // The tenant drops its license before installing the new one, so a failed install leaves
        // it unlicensed: put the old license back on it too.
        try {
            $result = $this->cloudApi()->send('POST', '/performAgentUpgradeOrDowngrade', [
                'licenseCode' => $oldLicense,
                'installation_path' => $installationPath,
                'product_id' => (int) $order->product,
                'old_lic_code' => $newLicense,
            ]);
            if (($result['status'] ?? null) !== 'success') {
                throw new Exception(json_encode($result) ?: 'unreadable reply');
            }
        } catch (Exception $exception) {
            Logger::exception(new Exception(sprintf('Restoring license %s on tenant %s failed, reinstall it by hand: %s', $oldLicense, $installationPath, $exception->getMessage()), 0, $exception));
        }
    }

    private function cloudApi(): CloudApi
    {
        return new CloudApi($this->client, FaveoCloud::first());
    }
}
