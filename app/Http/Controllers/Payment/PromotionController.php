<?php

namespace App\Http\Controllers\Payment;

use App\Http\Requests\Payment\PromotionRequest;
use App\Model\Order\Invoice;
use App\Model\Payment\PromoProductRelation;
use App\Model\Payment\Promotion;
use App\Model\Payment\PromotionType;
use App\Model\Product\Product;
use Darryldecode\Cart\CartCondition;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PromotionController extends BasePromotionController
{
    public $promotion;

    public $product;

    public $promoRelation;

    public $type;

    public $invoice;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $promotion = new Promotion();
        $this->promotion = $promotion;

        $product = new Product();
        $this->product = $product;

        $promoRelation = new PromoProductRelation();
        $this->promoRelation = $promoRelation;

        $type = new PromotionType();
        $this->type = $type;

        $invoice = new Invoice();
        $this->invoice = $invoice;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Response
     */
    public function index()
    {
        try {
            return view('themes.default1.payment.promotion.index');
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getPromotion()
    {
        $new_promotion = $this->promotion->select('code', 'type', 'id');

        return DataTables::of($new_promotion)
                            ->orderColumn('code', '-created_at $1')
                            ->orderColumn('type', '-created_at $1')
                            ->orderColumn('products', '-created_at $1')

                            ->addColumn('checkbox', function ($model) {
                                return "<input type='checkbox' class='promotion_checkbox'
                                 value=".$model->id.' name=select[] id=check>';
                            })
                        ->addColumn('code', function ($model) {
                            return ucfirst($model->code);
                        })
                        ->addColumn('type', function ($model) {
                            return $this->type->where('id', $model->type)->first()->name;
                        })
                        ->addColumn('products', function ($model) {
                            $selected = $this->promoRelation->select('product_id')
                            ->where('promotion_id', $model->id)->get();
                            $result = [];
                            foreach ($selected as $key => $select) {
                                $result[$key] = $this->product->where('id', $select->product_id)->first()->name;
                            }
                            if (! empty($result)) {
                                return implode(',', $result);
                            } else {
                                return 'None';
                            }
                        })
                        ->addColumn('action', function ($model) {
                            return '<a href='.url('promotions/'.$model->id.'/edit')
                            ." class='btn btn-sm btn-secondary btn-xs'".tooltip( __('message.edit'))."<i class='fa fa-edit' 
                            style='color:white;'> </i></a>";
                        })
                         ->filterColumn('code', function ($query, $keyword) {
                             $sql = 'code like ?';
                             $query->whereRaw($sql, ["%{$keyword}%"]);
                         })

                         ->rawColumns(['checkbox', 'code', 'products', 'action'])

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
            $product = $this->product->pluck('name', 'id')->toArray();
            $type = $this->type->pluck('name', 'id')->toArray();

            return view('themes.default1.payment.promotion.create', compact('product', 'type'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Response
     */
    public function store(PromotionRequest $request)
    {
        try {
            $startdate = date_create($request->input('start'));
            $start = date_format($startdate, 'Y-m-d H:m:i');
            $enddate = date_create($request->input('expiry'));
            $expiry = date_format($enddate, 'Y-m-d H:m:i');
            $this->promotion->code = $request->input('code');
            $this->promotion->type = $request->input('type');
            $this->promotion->value = $request->input('type') == 1 ? intval($request->input('value')).'%' : intval($request->input('value'));
            $this->promotion->uses = $request->input('uses');
            $this->promotion->start = $start;
            $this->promotion->expiry = $expiry;
            $this->promotion->save();
            $product = $request->input('applied');

            $this->promoRelation->create(['product_id' => $product, 'promotion_id' => $this->promotion->id]);

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
            $promotion = $this->promotion->where('id', $id)->first();
            $product = $this->product->pluck('name', 'id')->toArray();
            $type = $this->type->pluck('name', 'id')->toArray();
            $startDate = date('m/d/Y', strtotime($this->promotion->where('id', $id)->pluck('start')->first()));
            $expiryDate = date('m/d/Y', strtotime($this->promotion->where('id', $id)->pluck('expiry')->first()));
            $selectedProduct = $this->promoRelation
            ->where('promotion_id', $id)
            ->pluck('product_id', 'product_id')->toArray();

            return view(
                'themes.default1.payment.promotion.edit',
                compact('product', 'promotion', 'selectedProduct', 'type', 'startDate', 'expiryDate'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function update($id, PromotionRequest $request)
    {
        try {
            $startdate = date_create($request->input('start'));
            $start = date_format($startdate, 'Y-m-d H:m:i');
            $enddate = date_create($request->input('expiry'));
            $expiry = date_format($enddate, 'Y-m-d H:m:i');
            $promotion = $this->promotion->where('id', $id)->update([
                'code' => $request->input('code'),
                'type' => $request->input('type'),
                'value' => $request->input('type') == 2 ? intval($request->input('value')) : intval($request->input('value')).'%',
                'uses' => $request->input('uses'),
                'start' => $start,
                'expiry' => $expiry,
            ]);
            /* Delete the products has this id */
            $deletes = $this->promoRelation->where('promotion_id', $id)->get();
            foreach ($deletes as $delete) {
                $delete->delete();
            }
            /* Update the realtion details */
            $product = $request->input('applied');
            $this->promoRelation->create(['product_id' => $product, 'promotion_id' => $id]);

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
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
                    $promotion = $this->promotion->where('id', $id)->first();
                    if ($promotion) {
                        $promotion->delete();
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

    public function checkCode($code)
    {
        try {
            $promo = $this->getPromotionDetails($code);
            $codevalue = Promotion::where('code', $code)->first();

            if (! $codevalue) {
                throw new \Exception( __('message.invalid_promo_code'));
            }
            $promotion = $this->promotion->where('code', $code)->first();
            $uses = $promotion->uses;
            if (\Session::get('usage') == 1) {
                if (\Session::get('code') == $code) {
                    throw new \Exception( __('message.coupon_code_applied'));
                } elseif (\Session::get('usage') >= $uses) {
                    throw new \Exception(\Lang::get('message.usage-of-code-completed'));
                } else {
                    $productid = '';
                    $originalPrice = \Session::get('oldPrice');
                    foreach (\Cart::getContent() as $item) {
                        $productid = $item->id;
                    }
                    if ($productid && $originalPrice) {
                        \Cart::update($productid, [
                            'price' => $originalPrice,
                        ]);
                        \Session::forget('code');
                        \Session::forget('oldprice');
                        \Session::forget('usage');
                    }
                }
            }
            $validProductForPromo = $promo->relation->first()->product_id;
            $value = $this->findCostAfterDiscount($promo->id, $validProductForPromo, \Auth::user()->id);
            $productid = '';
            foreach (\Cart::getContent() as $item) {
                if ($item->id == $validProductForPromo) {
                    $productid = $item->id;
                    $original = $item->price;
                }
            }
            if ($productid) {
                \Session::put('usage', 1);
                \Session::put('code', $promo->code);
                \Session::put('codevalue', $promo->value);
                $coupon101 = new CartCondition([
                    'name' => $original,
                    'type' => 'coupon',
                    'value' => '-'.$promo->value,
                ]);
                \Cart::update($productid, [
                    'id' => $productid,
                    'price' => $value,

                    'conditions' => $coupon101,

                    // new item price, price can also be a string format like so: '98.67'
                ]);
                \Session::put('togglePrice', $coupon101->getName());
                \Session::put('productid', $productid);

                return redirect()->back()->with('success', __('message.coupon_code_applied_successfully'));
            } else {
                throw new \Exception(__('message.invalid_promo_code'));
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function checkNumberOfUses($code)
    {
        try {
            $promotion = $this->promotion->where('code', $code)->first();
            $uses = $promotion->uses;
            if ($uses == 1) {
                return 'success';
            }
            $used_number = $this->invoice->where('coupon_code', $code)->count();
            if ($uses >= $used_number) {
                return 'success';
            } else {
                return 'fails';
            }
        } catch (\Exception $ex) {
            throw new \Exception(\Lang::get('message.find-cost-error'));
        }
    }

    public function checkExpiry($code)
    {
        try {
            $promotion = $this->promotion->where('code', $code)->first();
            $start = $promotion->start;
            $end = $promotion->expiry;
            $now = \Carbon\Carbon::now()->format('Y-m-d H:m:i');
            $inv_cont = new \App\Http\Controllers\Order\InvoiceController();
            $getExpiryStatus = $inv_cont->getExpiryStatus($start, $end, $now);

            return $getExpiryStatus;
        } catch (\Exception $ex) {
            throw new \Exception(\Lang::get('message.check-expiry'));
        }
    }
}
