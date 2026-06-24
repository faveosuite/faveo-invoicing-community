<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Order\InvoiceController;
use App\Http\Requests\Payment\PromotionRequest;
use App\Model\Order\Invoice;
use App\Model\Payment\PromoProductRelation;
use App\Model\Payment\Promotion;
use App\Model\Payment\PromotionType;
use App\Model\Product\Product;
use DB;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
class PromotionController extends BasePromotionController
{
    /**
     * @var Promotion
     */
    public $promotion;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var PromoProductRelation
     */
    public $promoRelation;

    /**
     * @var PromotionType
     */
    public $type;

    /**
     * @var Invoice
     */
    public $invoice;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $promotion = new Promotion;
        $this->promotion = $promotion;

        $product = new Product;
        $this->product = $product;

        $promoRelation = new PromoProductRelation;
        $this->promoRelation = $promoRelation;

        $type = new PromotionType;
        $this->type = $type;

        $invoice = new Invoice;
        $this->invoice = $invoice;
    }

    public function checkNumberOfUses(mixed $code): string
    {
        try {
            /** @var Promotion $promotion */
            $promotion = $this->promotion->where('code', $code)->first();
            $uses = $promotion->uses;
            if ($uses == 1) {
                return 'success';
            }

            $used_number = $this->invoice->where('coupon_code', $code)->count();
            if ($uses > $used_number) {
                return 'success';
            }

            return 'fails';
        } catch (Exception) {
            throw new Exception(__('message.find-cost-error'));
        }
    }

    public function checkExpiry(mixed $code): ?string
    {
        try {
            /** @var Promotion $promotion */
            $promotion = $this->promotion->where('code', $code)->first();
            $start = $promotion->start;
            $end = $promotion->expiry;
            $now = Date::now()->format('Y-m-d H:m:i');
            $inv_cont = new InvoiceController;

            return $inv_cont->getExpiryStatus($start, $end, $now);
        } catch (Exception) {
            throw new Exception(__('message.check-expiry'));
        }
    }

    public function getAllPromotions(Request $request): JsonResponse
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
                    $query->where('code', 'like', sprintf('%%%s%%', $searchQuery))
                        ->orWhereHas('products', function (Builder $q) use ($searchQuery): void {
                            $q->where('name', 'like', sprintf('%%%s%%', $searchQuery));
                        })
                        ->orWhereHas('promotionType', function (Builder $q) use ($searchQuery): void {
                            $q->where('name', 'like', sprintf('%%%s%%', $searchQuery));
                        });
                });
            })
            ->orderBy('promotions.'.$sortField, $sortOrder)
            ->simplePaginate($limit);

        return successResponse('', $promotions);
    }

    public function getPromotion(mixed $promotionId, Request $request): JsonResponse
    {
        try {
            $promotion = Promotion::with([
                'promotionType:id,name',
                'products' => function ($q): void {
                    $q->select('products.id', 'products.name');
                },
            ])
                ->findOrFail($promotionId);

            return successResponse('', $promotion);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updatePromotionCode(mixed $promotionId, PromotionRequest $request): JsonResponse
    {
        try {
            /** @var Promotion $promotion */
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
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function promotionCodeCreate(PromotionRequest $request): JsonResponse
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
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function deleteBulkPromotions(Request $request): JsonResponse
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
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
