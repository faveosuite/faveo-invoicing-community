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
    public $group;

    public $feature;

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
     *
     * @return \Response
     */
    public function store(GroupRequest $request)
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
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, GroupRequest $request)
    {
        try {
            $group = $this->group->where('id', $id)->first();
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
                } elseif ($request->status == 0) {
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
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @return \Response
     */
    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $group = $this->group->where('id', $id)->first();

                    if ($group) {
                        $group->delete();
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */Lang::get('message.no-record').'
                </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }

                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>

                    <b>"./* @scrutinizer ignore-type */Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */Lang::get('message.success').'

                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */Lang::get('message.deleted-successfully').'
                </div>';
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */Lang::get('message.select-a-row').'
                </div>';
                //echo \Lang::get('message.select-a-row');
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }

    /**
     * Generate Slug url for A group.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-01-09T18:20:16+0530
     *
     * @param  Request  $request  Slug Url that is sent
     * @return string The Group Url
     */
    public function generateGroupUrl(Request $request)
    {
        if ($request->has('url')) {
            $url = $request->input('url');

            return $this->getGroupUrl($url);
        }
    }

    protected function getGroupUrl($url)
    {
        $slug = url('/').'/group/'.Str::slug($url, '-');
        echo $slug;
    }

//    This is for the client panel, change it to the client panel controllers, which does not have middleware admin.
    public function getAvailableGroups()
    {
        try {
            $groups = ProductGroup::select('id', 'name', 'pricing_templates_id')->where('hidden', '!=', 1)->get()->toArray();
            foreach ($groups as $group) {
                $grouped[$group['id']]['url'] = url('group/'.$group['pricing_templates_id'].'/'.$group['id']);
                $grouped[$group['id']]['name'] = $group['name'];
            }

            return successResponse(trans('message.success'), $grouped);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function getProductGroups(Request $request)
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = $request->input('sort-order', 'asc');
        $sortField = $request->input('sort-field', 'created_at');
        $limit = $request->input('limit', 10);

        $groups = ProductGroup::when($searchQuery, function ($query) use ($searchQuery): void {
            $query->where('name', 'like', "%{$searchQuery}%");
        })
            ->orderBy($sortField, $sortOrder)
            ->simplePaginate($limit);

        return successResponse('', $groups);
    }

    public function getGroup($groupId, Request $request)
    {
        try {
            return ProductGroup::with([
                'pricingTemplate:id,image,name',
                'product:id,name,group',
            ])->findOrFail($groupId);
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function updateGroup($groupId, GroupRequest $request)
    {
        try {
            $group = ProductGroup::findOrFail($groupId);

            // Get all visible, non-contact products
            $products = $group->product()
                ->where('hidden', 0)
                ->where('add_to_contact', 0)
                ->get();

            // Check if all products have both monthly and yearly plans
            $allProductsHavePlans = $products->every(function ($product) {
                $monthlyExists = Plan::where('product', $product->id)
                    ->whereIn('days', [30, 31])
                    ->exists();

                $yearlyExists = Plan::where('product', $product->id)
                    ->whereIn('days', [365, 366])
                    ->exists();

                return $monthlyExists && $yearlyExists;
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
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function groupCreate(GroupRequest $request)
    {
        try {
            ProductGroup::create($request->validated());

            return successResponse(__('message.saved-successfully'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function deleteBulkGroups(Request $request)
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
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }
}
