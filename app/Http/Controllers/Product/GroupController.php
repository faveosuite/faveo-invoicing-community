<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\GroupRequest;
use App\Model\Payment\Plan;
use App\Model\Product\ConfigurableOption;
use App\Model\Product\GroupFeatures;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Lang;

class GroupController extends Controller
{
    /**
     * @var \App\Model\Product\ProductGroup
     */
    public $group;

    /**
     * @var \App\Model\Product\GroupFeatures
     */
    public $feature;

    /**
     * @var \App\Model\Product\ConfigurableOption
     */
    public $config;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $group = new ProductGroup();
        $this->group = $group;

        $feature = new GroupFeatures();
        $this->feature = $feature;

        $config = new ConfigurableOption();
        $this->config = $config;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GroupRequest $request): mixed
    {
        $this->validate($request, [
            'name' => 'required',
            'pricing_templates_id' => 'required',
        ], [
            'pricing_templates_id.required' => __('message.please_select_template'),
            'name.required' => __('validation.bundle.name.required'),
        ]);

        try {
            $data = $request->input();
            $this->group->fill($request->input())->save();
            $this->group->refresh();

            return back()->with('success', Lang::get('message.saved-successfully'));
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id, GroupRequest $request)
    {
        try {
            /** @var \App\Model\Product\ProductGroup $group */
            $group = $this->group->where('id', $id)->firstOrFail();
            $products = Product::where('group', $id)->where('hidden', '0')->where('add_to_contact', '0')->get();

            // Check if all products have both monthly and yearly plans
            $allProductsHavePlans = true;

            foreach ($products as $product) {
                $monthlyPlan = Plan::where('product', $product->id)->where('status', 1)->where('days', 30)->first();
                $yearlyPlan = Plan::where('product', $product->id)->where('status', 1)->where(function ($q): void {
                    $q->where('days', 365)->orWhere('days', 366);
                })->first();

                if (! $monthlyPlan || ! $yearlyPlan) {
                    $allProductsHavePlans = false;
                    break; // No need to continue checking
                }
            }

            if (! $products->isEmpty() && $allProductsHavePlans) {
                if ($request->status == 1) {
                    $group->fill($request->input())->save();
                    Product::where('group', $id)->update(['status' => 1]);

                    return back()->with('success', Lang::get('message.updated-successfully'));
                }

                if ($request->status == 0) {
                    $group->fill($request->input())->save();
                    Product::where('group', $id)->update(['status' => 0]);

                    return back()->with('success', Lang::get('message.updated-successfully'));
                }
            } elseif ($request->status == 0) {
                $group->fill($request->input())->save();
                Product::where('group', $id)->update(['status' => 0]);

                return back()->with('success', Lang::get('message.updated-successfully'));
            }

            return back()->with('fails', __('message.all_products_monthly_yearly_plan'));
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    protected function getGroupUrl(mixed $url): void
    {
        $slug = url('/').'/group/'.Str::slug($url, '-');
        echo $slug;
    }

//    This is for the client panel, change it to the client panel controllers, which does not have middleware admin.
    public function getProductGroups(Request $request): \Illuminate\Http\JsonResponse
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = $request->input('sort-order', 'asc');
        $sortField = $request->input('sort-field', 'created_at');
        $limit = $request->input('limit', 10);

        $groups = ProductGroup::when($searchQuery, function ($query) use ($searchQuery): void {
            $query->where('name', 'like', sprintf('%%%s%%', $searchQuery));
        })
            ->orderBy($sortField, $sortOrder)
            ->simplePaginate($limit);

        return successResponse('', $groups);
    }

    public function getGroup(mixed $groupId, Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $group = ProductGroup::with([
                'pricingTemplate:id,image,name',
                'product:id,name,group',
            ])->findOrFail($groupId);
            return successResponse('', $group);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updateGroup(mixed $groupId, GroupRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            /** @var \App\Model\Product\ProductGroup $group */
            $group = ProductGroup::findOrFail($groupId);

            // Get all visible, non-contact products
            $products = $group->product()
                ->where('hidden', 0)
                ->where('add_to_contact', 0)
                ->get();

            // Check if all products have both monthly and yearly plans
            $allProductsHavePlans = $products->every(function ($product): bool {
                return Plan::where('product', $product->id)
                    ->whereIn('days', [30, 31])
                    ->exists() && Plan::where('product', $product->id)
                    ->whereIn('days', [365, 366])
                    ->exists();
            });

            // If enabling the group, ensure all products have plans
            if ($request->status == 1 && ! $products->isEmpty() && ! $allProductsHavePlans) {
                return errorResponse(__('message.all_products_monthly_yearly_plan'));
            }

            // Update group
            $group->update($request->validated());

            // Update product statuses
            $productStatus = $request->status == 1 && $allProductsHavePlans ? 1 : 0;
            $group->product()->update(['status' => $productStatus]);

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function groupCreate(GroupRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            ProductGroup::create($request->validated());

            return successResponse(__('message.saved-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function deleteBulkGroups(Request $request): \Illuminate\Http\JsonResponse
    {
        $ids = $request->input('select', []);

        if (empty($ids)) {
            return errorResponse(__('message.select-a-row'));
        }

        try {
            DB::transaction(function () use ($ids): void {
                $groups = ProductGroup::whereIn('id', $ids)->get();

                foreach ($groups as $group) {
                    $group->delete();
                }
            });

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
