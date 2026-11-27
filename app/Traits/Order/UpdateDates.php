<?php

namespace App\Traits\Order;

use App\Model\Product\Subscription;
use App\Services\SubscriptionRenewalService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

trait UpdateDates
{
    public function updateLicenseDetails(Request $request): JsonResponse
    {
        $this->validate($request, [
            'orderid' => 'required',
        ]);

        try {
            $service = resolve(SubscriptionRenewalService::class);
            $sub = Subscription::where('order_id', $request->input('orderid'))->firstOrFail();

            // input => subscription column
            $dateFields = ['update_end' => 'update_ends_at', 'subscription_end' => 'ends_at', 'support_end' => 'support_ends_at'];
            foreach ($dateFields as $input => $column) {
                if ($request->filled($input)) {
                    $service->setDate($sub, $column, $this->parseDate($request->input($input)));
                }
            }

            if ($request->filled('limit')) {
                $service->updateInstallationLimit($sub, (int) $request->input('limit'));
            }

            return successResponse(__('message.updated-successfully'));
        } catch (ModelNotFoundException $exception) {
            return errorResponse(__('message.record_not_found'));
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    private function parseDate(string $date): string
    {
        return Date::createFromFormat('m/d/Y', $date)?->format('Y-m-d H:i:s') ?? ''; // @phpstan-ignore nullsafe.neverNull
    }
}
