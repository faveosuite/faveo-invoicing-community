<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\GroupRequest;
use App\Model\Common\PricingTemplate;
use App\Model\Payment\Plan;
use App\Model\Product\ConfigurableOption;
use App\Model\Product\GroupFeatures;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use Illuminate\Http\Request;

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
     * Display a listing of the resource.
     *
     * @return \Response
     */
    public function index()
    {
        try {
            return view('themes.default1.product.group.index');
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getGroups()
    {
        $product_group = ProductGroup::select('id', 'name');

        return \DataTables::of($product_group)
        // return \Datatable::of($this->group->select('id', 'name')->get())
                         ->orderColumn('name', '-id $1')
                       ->addColumn('checkbox', function ($model) {
                           return "<input type='checkbox' class='group_checkbox' 
                            value=".$model->id.' name=select[] id=check>';
                       })

                        ->addColumn('name', function ($model) {
                            return ucfirst($model->name);
                        })
                        // ->showColumns('name')

                        ->addColumn('action', function ($model) {
                            return '<a href='.url('groups/'.$model->id.'/edit').
                            " class='btn btn-sm btn-secondary btn-xs'".tooltip(__('message.edit'))."<i class='fa fa-edit' 
                            style='color:white;'> </i></a>";
                        })
                          ->filterColumn('name', function ($query, $keyword) {
                              $sql = 'name like ?';
                              $query->whereRaw($sql, ["%{$keyword}%"]);
                          })
                        ->rawColumns(['checkbox', 'name',  'action'])
                        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Response
     */
    public function create()
    {
        try {
            $template = PricingTemplate::select('image', 'id', 'name')->first();

            return view('themes.default1.product.group.create', compact('template'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
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

            return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Response
     */
    public function edit($id)
    {
        try {
            $group = $this->group->where('id', $id)->first();
            $selectedTemplate = $group->pricing_templates_id;
            $template = PricingTemplate::first();

            return view('themes.default1.product.group.edit', compact('group', 'selectedTemplate', 'template'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
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
                $monthlyPlan = Plan::where('product', $product->id)->where('days', 30)->first();
                $yearlyPlan = Plan::where('product', $product->id)->where('days', 365)->orWhere('days', 366)->first();

                if (! $monthlyPlan || ! $yearlyPlan) {
                    $allProductsHavePlans = false;
                    break; // No need to continue checking
                }
            }

            if (! $products->isEmpty() && $allProductsHavePlans) {
                if ($request->status == 1) {
                    $group->fill($request->input())->save();
                    Product::where('group', $id)->update(['status' => 1]);

                    return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
                } elseif ($request->status == 0) {
                    $group->fill($request->input())->save();
                    Product::where('group', $id)->update(['status' => 0]);

                    return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
                }
            } elseif ($request->status == 0) {
                $group->fill($request->input())->save();
                Product::where('group', $id)->update(['status' => 0]);

                return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
            }

            return redirect()->back()->with('fails', __('message.all_products_monthly_yearly_plan'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
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
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }
                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>

                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.success').'

                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.select-a-row').'
                </div>';
                //echo \Lang::get('message.select-a-row');
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */\Lang::get('message.failed').'
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
        $slug = url('/').'/group/'.str_slug($url, '-');
        echo $slug;
    }

    public function getAvailableGroups(){
        try {
            $groups = \App\Model\Product\ProductGroup::select('id', 'name', 'pricing_templates_id')->where('hidden', '!=', 1)->get()->toArray();
            foreach ($groups as $group) {
                $grouped[$group['id']]['url']=url('group/'.$group['pricing_templates_id'].'/'.$group['id']);
                $grouped[$group['id']]['name']=$group['name'];
            }
            return successResponse(trans('message.success'), $grouped);
        }catch (\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }
}
