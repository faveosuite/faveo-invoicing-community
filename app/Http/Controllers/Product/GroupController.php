<?php

namespace App\Http\Controllers\Product;

use App\Facades\Attach;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\GroupRequest;
use App\Model\Payment\Plan;
use App\Model\Product\ConfigurableOption;
use App\Model\Product\GroupFeatures;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use App\Services\Seo\SeoFileGenerator;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class GroupController extends Controller
{
    /**
     * @var ProductGroup
     */
    public $group;

    /**
     * @var GroupFeatures
     */
    public $feature;

    /**
     * @var ConfigurableOption
     */
    public $config;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $group = new ProductGroup;
        $this->group = $group;

        $feature = new GroupFeatures;
        $this->feature = $feature;

        $config = new ConfigurableOption;
        $this->config = $config;
    }

    protected function getGroupUrl(mixed $url): void
    {
        $slug = url('/').'/group/'.Str::slug($url, '-');
        echo $slug;
    }

    //    This is for the client panel, change it to the client panel controllers, which does not have middleware admin.
    public function getProductGroups(Request $request): JsonResponse
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

    public function getGroup(string $groupId, Request $request): JsonResponse
    {
        try {
            $group = ProductGroup::with([
                'pricingTemplate:id,image,name',
                'product:id,name,group',
            ])->findOrFail($groupId);

            $data = $group->toArray();
            $data['og_image'] = $group->og_image ? Attach::getUrlPath('images/'.$group->og_image) : null;

            return successResponse('', $data);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updateGroup(string $groupId, GroupRequest $request): JsonResponse
    {
        try {
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

            // Update group (og_image is an uploaded file, handled separately below)
            $data = $request->validated();
            unset($data['og_image']);
            if ($request->hasFile('og_image')) {
                $data['og_image'] = basename((string) Attach::put('images', $request->file('og_image'), null, true));
            }
            $group->update($data);

            // Update product statuses
            $productStatus = $request->status == 1 && $allProductsHavePlans ? 1 : 0;
            $group->product()->update(['status' => $productStatus]);

            $this->regenerateSeoFiles();

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function groupCreate(GroupRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            unset($data['og_image']);
            if ($request->hasFile('og_image')) {
                $data['og_image'] = basename((string) Attach::put('images', $request->file('og_image'), null, true));
            }
            ProductGroup::create($data);

            $this->regenerateSeoFiles();

            return successResponse(__('message.saved-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function deleteBulkGroups(Request $request): JsonResponse
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

            $this->regenerateSeoFiles();

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    private function regenerateSeoFiles(): void
    {
        try {
            app(SeoFileGenerator::class)->generateAll();
        } catch (Throwable $throwable) {
            report($throwable);
        }
    }
}
