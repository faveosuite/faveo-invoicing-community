<?php

namespace App\Traits\Order;

use App\Model\Product\Subscription;
use App\Services\SubscriptionRenewalService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

trait UpdateDates
{
    public function updateLicenseDetails(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, ['orderid' => 'required']);

        try {
            $service = resolve(SubscriptionRenewalService::class);
            $sub = Subscription::where('order_id', $request->input('orderid'))->firstOrFail();

            if ($request->filled('update_end')) {
                $service->setDate($sub, 'update_ends_at', $this->parseDate($request->input('update_end')));
            }

            if ($request->filled('subscription_end')) {
                $service->setDate($sub, 'ends_at', $this->parseDate($request->input('subscription_end')));
            }

            if ($request->filled('support_end')) {
                $service->setDate($sub, 'support_ends_at', $this->parseDate($request->input('support_end')));
            }

            if ($request->filled('limit')) {
                $service->updateInstallationLimit($sub, (int) $request->input('limit'));
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
