<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Order\InvoiceController;
use App\Http\Requests\Payment\PromotionRequest;
use App\Model\Order\Invoice;
use App\Model\Payment\PromoProductRelation;
use App\Model\Payment\Promotion;
use App\Model\Payment\PromotionType;
use App\Model\Product\Product;
use Auth;
use DB;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Lang;
use Session;

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

            return back()->with('success', Lang::get('message.saved-successfully'));
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
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
            $promotion = $this->promotion->where('id', $id)->first();
            $promotion->update([
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

            return back()->with('success', Lang::get('message.updated-successfully'));
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
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
            if ($uses > $used_number) {
                return 'success';
            } else {
                return 'fails';
            }
        } catch (Exception) {
            throw new Exception(Lang::get('message.find-cost-error'));
        }
    }

    public function checkExpiry($code)
    {
        try {
            $promotion = $this->promotion->where('code', $code)->first();
            $start = $promotion->start;
            $end = $promotion->expiry;
            $now = Date::now()->format('Y-m-d H:m:i');
            $inv_cont = new InvoiceController();
            $getExpiryStatus = $inv_cont->getExpiryStatus($start, $end, $now);

            return $getExpiryStatus;
        } catch (Exception) {
            throw new Exception(Lang::get('message.check-expiry'));
        }
    }

    public function getAllPromotions(Request $request)
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = $request->input('sort-order', 'asc');
        $sortField = $request->input('sort-field', 'created_at');
        $limit = $request->input('limit', 10);

        $promotions = Promotion::with([
            'promotionType:id,name',
            'products' => function ($q): void {
                $q->select('products.id', 'products.name');
            },
        ])
            ->when($searchQuery, function ($q) use ($searchQuery): void {
                $q->where(function (Builder $query) use ($searchQuery): void {
                    $query->where('code', 'like', "%{$searchQuery}%")
                        ->orWhereHas('products', function (Builder $q) use ($searchQuery): void {
                            $q->where('name', 'like', "%{$searchQuery}%");
                        })
                        ->orWhereHas('promotionType', function (Builder $q) use ($searchQuery): void {
                            $q->where('name', 'like', "%{$searchQuery}%");
                        });
                });
            })
            ->orderBy('promotions.'.$sortField, $sortOrder)
            ->simplePaginate($limit);

        return successResponse('', $promotions);
    }

    public function getPromotion($promotionId, Request $request)
    {
        try {
            return Promotion::with([
                'promotionType:id,name',
                'products' => function ($q): void {
                    $q->select('products.id', 'products.name');
                },
            ])
            ->findOrFail($promotionId);
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function updatePromotionCode($promotionId, PromotionRequest $request)
    {
        try {
            $promotion = Promotion::findOrFail($promotionId);

            $start = Date::parse($request->input('start'))->format('Y-m-d H:i:s');
            $expiry = Date::parse($request->input('expiry'))->format('Y-m-d H:i:s');

            // Update promotion fields
            $promotion->update([
                'code' => $request->input('code'),
                'type' => $request->input('type'),
                'value' => $request->input('type') == 2
                    ? intval($request->input('value'))
                    : intval($request->input('value')).'%',
                'uses' => $request->input('uses'),
                'start' => $start,
                'expiry' => $expiry,
            ]);

            // Delete old product relation
            PromoProductRelation::where('promotion_id', $promotion->id)->delete();

            // Create new product relation
            PromoProductRelation::create([
                'promotion_id' => $promotion->id,
                'product_id' => $request->input('applied'),
            ]);

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function promotionCodeCreate(PromotionRequest $request)
    {
        try {
            // Format start and expiry dates
            $start = Date::parse($request->input('start'))->format('Y-m-d H:i:s');
            $expiry = Date::parse($request->input('expiry'))->format('Y-m-d H:i:s');

            // Create the promotion
            $promotion = Promotion::create([
                'code' => $request->input('code'),
                'type' => $request->input('type'),
                'value' => $request->input('type') == 1
                    ? intval($request->input('value')).'%'
                    : intval($request->input('value')),
                'uses' => $request->input('uses'),
                'start' => $start,
                'expiry' => $expiry,
            ]);

            // Create the product relation
            PromoProductRelation::create([
                'promotion_id' => $promotion->id,
                'product_id' => $request->input('applied'),
            ]);

            return successResponse(__('message.created-successfully'));
        } catch (Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function deleteBulkPromotions(Request $request)
    {
        $ids = $request->input('select', []);

        if (empty($ids)) {
            return errorResponse(__('message.select-a-row'));
        }

        try {
            DB::transaction(function () use ($ids): void {
                $promotions = Promotion::whereIn('id', $ids)->get();

                foreach ($promotions as $promotion) {
                    $promotion->delete();
                }
            });

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }
}
