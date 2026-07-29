<?php

namespace App\Traits\Order;

use App\Model\Product\Subscription;
use App\Services\SubscriptionRenewalService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

trait UpdateDates
{
    public function updateLicenseDetails(Request $request): JsonResponse
    {
        $this->validate($request, ['orderid' => 'required']);

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

            // Every requested date field was blocked by this product's license
            // type — nothing actually changed, so this must not read as success.
            if ($skipped && count($skipped) === $requested) {
                return errorResponse(__('message.fields_not_permitted', ['fields' => implode(', ', $skipped)]));
            }

            if ($skipped) {
                return successResponse(__('message.some_fields_not_permitted', ['fields' => implode(', ', $skipped)]));
            }

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    private function parseDate(string $date): string
    {
        return Date::createFromFormat('m/d/Y', $date)?->format('Y-m-d H:i:s') ?? ''; // @phpstan-ignore nullsafe.neverNull
    }
}
