<?php

namespace App\Http\Controllers;

use App\Model\CloudDataCenters;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Services\Payment\FreeTrialService;
use Illuminate\Http\Request;
use Logger;
use RuntimeException;
use Throwable;

class FreeTrailController extends Controller
{
    public function __construct(
        private readonly FreeTrialService $freeTrialService,
    ) {
        $this->middleware('auth')->except('getCloudProducts');
    }

    public function startTrial(Request $request)
    {
        $request->validate([
            'domain' => ['required', 'regex:/^[a-zA-Z0-9]+$/u'],
            'product_id' => ['required', 'integer'],
        ], [
            'domain.regex' => __('validation.special_characters_not_allowed'),
        ]);

        $cloudProduct = CloudProducts::where('cloud_product', $request->integer('product_id'))->first();

        if (! $cloudProduct) {
            return errorResponse(__('message.cannot_find_product'));
        }

        $user = auth()->user();

        try {
            $this->freeTrialService->checkEligibility($user, $cloudProduct);
            $result = $this->freeTrialService->provision($user, $request->input('domain'), $cloudProduct);

            return successResponse(__('message.free_trial_started'), $result);
        } catch (RuntimeException $e) {
            return errorResponse($e->getMessage());
        } catch (Throwable $e) {
            Logger::exception($e);

            return errorResponse(__('message.cannot_generate_freetrial_cloud_instance'));
        }
    }

    public function getCloudProducts()
    {
        $cloudProductIds = cloudPopupProducts();

        if (empty($cloudProductIds)) {
            return successResponse('', [
                'cloud_subdomain' => cloudSubDomain() ?? '',
                'data_centers' => [],
                'products' => [],
            ]);
        }

        $cloudPlans = CloudProducts::whereIn('cloud_product', $cloudProductIds)
            ->pluck('cloud_free_plan', 'cloud_product');

        $products = Product::whereIn('id', $cloudProductIds)
            ->where('hidden', '!=', 1)
            ->orderBy('id')
            ->get();

        return successResponse('', [
            'cloud_subdomain' => cloudSubDomain() ?? '',
            'data_centers' => CloudDataCenters::select('id', 'cloud_countries', 'cloud_state')->get()
                ->map(fn ($dc) => [
                    'id' => $dc->id,
                    'name' => trim($dc->cloud_countries.($dc->cloud_state ? ', '.$dc->cloud_state : '')),
                ])->values(),
            'products' => $products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'default_plan_id' => $cloudPlans->get($p->id),
            ])->values(),
        ]);
    }
}
