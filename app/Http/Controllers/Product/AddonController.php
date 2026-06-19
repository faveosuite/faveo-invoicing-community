<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\AddonRequest;
use App\Model\Payment\Plan;
use App\Model\Product\Addon;
use App\Model\Product\Product;
use App\Model\Product\ProductAddonRelation;
use Exception;
use Illuminate\Http\Request;
use Lang;

class AddonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $product = new Product();
        $this->product = $product; // @phpstan-ignore property.notFound

        $plan = new plan();
        $this->plan = $plan; // @phpstan-ignore property.notFound

        $addon = new Addon();
        $this->addon = $addon; // @phpstan-ignore property.notFound
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): mixed
    {
        try {
            return view('themes.default1.product.addon.index'); // @phpstan-ignore argument.type
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): mixed
    {
        try {
            $product = $this->product->pluck('name', 'id')->toArray(); // @phpstan-ignore property.notFound
            $subscription = $this->plan->pluck('name', 'id')->toArray(); // @phpstan-ignore property.notFound

            //dd($subscription);
            return view('themes.default1.product.addon.create', compact('product', 'subscription')); // @phpstan-ignore argument.type
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddonRequest $request): mixed
    {
        try {
            $this->addon->fill($request->input())->save(); // @phpstan-ignore property.notFound
            $products = $request->input('products');
            $relation = new ProductAddonRelation();
            if (is_array($products)) {
                foreach ($products as $product) {
                    if ($product) {
                        $relation->create(['addon_id' => $this->addon->id, 'product_id' => $product]); // @phpstan-ignore property.notFound
                    }
                }
            }

            return back()->with('success', __('message.saved-successfully'));
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id): mixed
    {
        try {
            $product = $this->product->pluck('name', 'id')->toArray(); // @phpstan-ignore property.notFound
            $subscription = $this->plan->pluck('name', 'id')->toArray(); // @phpstan-ignore property.notFound
            $relation = new ProductAddonRelation();
            $relation = $relation->where('addon_id', $id)->pluck('product_id')->toArray();
            $addon = $this->addon->where('id', $id)->first(); // @phpstan-ignore property.notFound

            return view('themes.default1.product.addon.edit', compact('product', 'addon', 'subscription', 'relation')); // @phpstan-ignore argument.type
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function update($id, AddonRequest $request): mixed
    {
        try {
            $addon = $this->addon->where('id', $id)->first(); // @phpstan-ignore property.notFound
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

            return back()->with('success', __('message.updated-successfully'));
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request): void
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $addon = $this->addon->where('id', $id)->first(); // @phpstan-ignore property.notFound
                    if ($addon) {
                        $addon->delete();
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".(string) __('message.alert').'!</b> '.
                    (string) __('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.(string) __('message.no-record').'
                </div>';
                        //echo \__('message.no-record') . '  [id=>' . $id . ']';
                    }
                }

                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".(string) __('message.alert').'!</b> '.
                    (string) __('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.(string) __('message.deleted-successfully').'
                </div>';
            } else {
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".(string) __('message.alert').'!</b> '.
                    (string) __('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.(string) __('message.select-a-row').'
                </div>';
                //echo \__('message.select-a-row');
            }
        } catch (Exception $exception) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".(string) __('message.alert').'!</b> '.
                    (string) __('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$exception->getMessage().'
                </div>';
        }
    }
}
