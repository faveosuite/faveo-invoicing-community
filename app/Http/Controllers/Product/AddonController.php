<?php

namespace App\Http\Controllers\Product;

use Exception;
use Lang;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\AddonRequest;
use App\Model\Payment\Plan;
use App\Model\Product\Addon;
use App\Model\Product\Product;
use App\Model\Product\ProductAddonRelation;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $product = new Product();
        $this->product = $product;
        $plan = new plan();
        $this->plan = $plan;
        $addon = new Addon();
        $this->addon = $addon;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Response
     */
    public function index()
    {
        try {
            return view('themes.default1.product.addon.index');
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Response
     */
    public function create()
    {
        try {
            $product = $this->product->pluck('name', 'id')->toArray();
            $subscription = $this->plan->pluck('name', 'id')->toArray();

            //dd($subscription);
            return view('themes.default1.product.addon.create', compact('product', 'subscription'));
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Response
     */
    public function store(AddonRequest $request)
    {
        try {
            $this->addon->fill($request->input())->save();
            $products = $request->input('products');
            $relation = new ProductAddonRelation();
            if (is_array($products)) {
                foreach ($products as $product) {
                    if ($product) {
                        $relation->create(['addon_id' => $this->addon->id, 'product_id' => $product]);
                    }
                }
            }

            return back()->with('success', Lang::get('message.saved-successfully'));
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Response
     */
    public function show($id)
    {
        //
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
            $product = $this->product->pluck('name', 'id')->toArray();
            $subscription = $this->plan->pluck('name', 'id')->toArray();
            $relation = new ProductAddonRelation();
            $relation = $relation->where('addon_id', $id)->pluck('product_id')->toArray();
            $addon = $this->addon->where('id', $id)->first();

            return view('themes.default1.product.addon.edit', compact('product', 'addon', 'subscription', 'relation'));
        } catch (Exception $e) {
            return back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function update($id, AddonRequest $request)
    {
        try {
            $addon = $this->addon->where('id', $id)->first();
            $addon->fill($request->input())->save();

            $products = $request->input('products');
            $relation = new ProductAddonRelation();
            if (is_array($products)) {
                $delete = $relation->where('addon_id', $id)->get();

                foreach ($delete as $del) {
                    $del->delete();
                }

                foreach ($products as $product) {
                    if ($product) {
                        $relation->create(['addon_id' => $addon->id, 'product_id' => $product]);
                    }
                }
            }

            return back()->with('success', Lang::get('message.updated-successfully'));
        } catch (Exception $e) {
            return back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $addon = $this->addon->where('id', $id)->first();
                    if ($addon) {
                        $addon->delete();
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
}
