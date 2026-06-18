<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\PageController;
use App\Http\Controllers\Product\ProductController;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use Exception;
use Illuminate\Http\Request;
use Lang;
use Logger;
use Session;

class TemplateController extends Controller
{
    /**
     * @var \App\Model\Common\Template
     */
    public $template;

    /**
     * @var \App\Model\Common\TemplateType
     */
    public $type;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $template = new Template();
        $this->template = $template;

        $type = new TemplateType();
        $this->type = $type;
    }

    public function getTemplates(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $search = $request->input('search-query', '');
            $sortField = $request->input('sort-field', 'name');
            $sortOrder = $request->input('sort-order', 'asc');
            $limit = (int) $request->input('limit', 10);

            $allowedSort = ['name', 'id'];
            if (! in_array($sortField, $allowedSort)) {
                $sortField = 'name';
            }

            $typeNames = TemplateType::pluck('name', 'id');

            $paginated = $this->template
                ->select('id', 'name', 'type')
                ->when($search, fn ($q) => $q->where('name', 'like', sprintf('%%%s%%', $search)))
                ->orderBy($sortField, $sortOrder === 'desc' ? 'desc' : 'asc')
                ->paginate($limit);

            $paginated->getCollection()->transform(fn ($t): array => [
                'id' => $t->id,
                'name' => $t->name,
                'type' => $typeNames[$t->type] ?? '',
            ]);

            return successResponse('', $paginated);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_fetch_templates'));
        }
    }

    public function create(): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        try {
            $controller = new ProductController();
            $url = $controller->GetMyUrl();
            $i = $this->template->orderBy('created_at', 'desc')->first()->id + 1;
            $cartUrl = $url.'/'.$i;
            $type = $this->type->pluck('name', 'id')->toArray();

            return view('themes.default1.common.template.create', compact('type', 'cartUrl')); // @phpstan-ignore argument.type
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    public function store(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'data' => 'required',
            'type' => 'required',
            'reply_email' => 'required',
        ]);

        try {
            $this->template->fill($request->input())->save();

            return back()->with('success', Lang::get('message.saved-successfully'));
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    public function showTemplate(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $shortcodes = config('transform');
            $tooltips = config('shortcodes');

            $template = $this->template->find($id);

            if (! $template) {
                return errorResponse(__('message.template_not_found'));
            }

            $type = $this->type->pluck('name', 'id')->toArray();
            $templateType = TemplateType::find($template->type);
            $shortcodeName = $templateType ? $templateType->name : null;
            $codes = null;
            if ($shortcodeName && array_key_exists($shortcodeName, $shortcodes)) {
                $codes = $shortcodes[$shortcodeName];
            }

            $templateIdData = [
                'type' => $type,
                'template' => $template,
                'codes' => $codes,
                'tooltips' => $tooltips,
            ];

            return successResponse(__('message.templates_fetched_successfully'), $templateIdData);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_fetch_particular_template'));
        }
    }

    public function updateTemplate(int $id, \Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => ['required'],
            'data' => ['required'],
            'type' => ['required'],
        ], [
            'name.required' => __('validation.auth_controller.name_required'),
            'data.required' => __('message.content_required'),
            'type.required' => __('message.template_type_required'),
        ]);
        try {
            $template = $this->template->find($id);
            if (! $template) {
                return errorResponse(__('message.template_not_found'));
            }

            $template->fill($request->all())->save();

            return successResponse(__('message.template_update_success'), $template);
        } catch (Exception) {
            return errorResponse(__('message.template_update_error'));
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
                    $template = $this->template->where('id', $id)->first();
                    if ($template) {
                        $template->delete();
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
                    <b>"./* @scrutinizer ignore-type */Lang::get('message.alert').
                    '!</b> './* @scrutinizer ignore-type */Lang::get('message.success').'
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
            }
        } catch (Exception $exception) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$exception->getMessage().'
                </div>';
        }
    }

    public function plans(string $url, int $id): string|\Illuminate\Http\RedirectResponse
    {
        try {
            $product = Product::find($id);
            if (! $product || $product->add_to_contact == 1) {
                return '';
            }

            $plansData = $this->prices($id);
            if ($plansData instanceof \Illuminate\Http\RedirectResponse || empty($plansData)) {
                return '';
            }

            $list = $this->getPriceList($id);

            $priceList = $list['prices'];

            $cheapestPlanId = $list['cheapestPlanId'];

            $planOptions = '';

            foreach ($priceList as $planId => $planPrice) {
                $description = $plansData[$planId]['description'] ?? '';
                $price = $plansData[$planId]['price'] ?? '';
                $selected = $planId == $cheapestPlanId ? 'selected' : '';
                $planOptions .= sprintf(
                    '<option value="%s" data-price="%s" data-description="%s" %s>%s</option>',
                    htmlspecialchars((string) $planId),
                    htmlspecialchars((string) $planPrice),
                    htmlspecialchars($description),
                    $selected,
                    htmlspecialchars($price)
                );
            }

            $planClass = ($product->status != 1) ? 'stylePlan' : 'planhide';
            $planForm = '<select name="subscription" class="'.$planClass.'">'.$planOptions.'</select>';

            return (string) html()->form('GET', $url)->open() // @phpstan-ignore cast.string
                .$planForm
                .html()->input('hidden', 'id')->value((string) $id);
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    /**
     * Gets the least amount to be displayed on pricing page on the top.
     *
     * @param  int  $id  Product id
     * @return string Product price with html
     */
    public function leastAmount(int $id): string|\Illuminate\Http\RedirectResponse
    {
        try {
            $cost = 'Free';
            $plans = Plan::where('product', $id)->where('status', 1)->whereIn('days', [30, 31])->get();
            $prices = [];
            $currency = '';
            foreach ($plans as $plan) {
                $planDetails = userCurrencyAndPrice('', $plan);
                $add_price = $planDetails['plan']->add_price ?? 0;

                // Only consider non-zero prices
                if ($add_price > 0) {
                    $prices[] = $add_price;
                    $currency = $planDetails['currency'];
                }
            }

            if ($prices !== []) {
                $minPrice = min($prices);
                $cost = new PageController()->currencyFormatWithSpan($minPrice, $currency);
            }

            return $cost;
        } catch (Exception $exception) {
            return back()->with('fails', $exception->getMessage());
        }
    }

    public function getPrice(string $months, array $price, string $priceDescription, \App\Model\Payment\Plan $value, float|int $cost, string $currency, float|int|null $offer, \App\Model\Product\Product $product): array
    {
        if (isset($offer)) {
            $cost -= ($offer / 100) * $cost;
        }

        $price1 = currencyFormat($cost, $code = $currency);
        $months = $cost == 0 ? $priceDescription : $months;
        $priceDescription = $priceDescription == '' ? $months : $priceDescription;
        $price[$value->id]['cost'] = rounding($cost);
        $price[$value->id]['price'] = $price1.' '.$priceDescription;
        $price[$value->id]['description'] = $priceDescription != '' ? $priceDescription : '';

        return $price;
    }

    /**
     * @return array<mixed>|\Illuminate\Http\RedirectResponse
     */
    public function prices(int $id): array|\Illuminate\Http\RedirectResponse
    {
        try {
            $plans = Plan::where('product', $id)
                ->where('status', 1)
                ->with(['planPrice', 'periods'])
                ->orderByDesc('id')
                ->get();

            $result = [];

            // Cache product outside loop to avoid repeated queries
            $product = Product::find($id);
            if (! $product) {
                return $result;
            }

            $cloudPopupProducts = cloudPopupProducts();

            foreach ($plans as $plan) {
                $currencyAndSymbol = userCurrencyAndPrice('', $plan);
                $currency = $currencyAndSymbol['currency'];
                $planData = $currencyAndSymbol['plan'];

                if (! $planData) {
                    continue;
                }

                $rawOffer = PlanPrice::where('plan_id', $plan->id)
                    ->where('currency', $currency)
                    ->value('offer_price');
                $offer = is_numeric($rawOffer) ? (float) $rawOffer : null;

                $cost = rounding($planData->add_price);
                $priceDescription = $planData->price_description;
                $months = $plan->period ? $plan->period->name : ''; // @phpstan-ignore property.notFound

                $includePrice =
                    (! in_array($product->id, $cloudPopupProducts)) ||
                    (in_array($product->id, $cloudPopupProducts) && $cost != 0);

                if ($includePrice) {
                    $result = $this->getPrice(
                        $months,
                        $result,
                        $priceDescription,
                        $plan,
                        $cost,
                        $currency,
                        $offer,
                        $product
                    );
                }
            }

            return $result;
        } catch (Exception $exception) {
            Logger::exception($exception);

            return back()->with('fails', $exception->getMessage());
        }
    }

    public function toggle(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        Session::put('toggleState', $request->toggleState === 'selected' ? 'yearly' : 'monthly');

        return successResponse('');
    }

    public function getPriceList(int $id): array
    {
        try {
            $plans = Plan::where('product', $id)->where('status', 1)->orderBy('id', 'desc')->get();
            $prices = [];
            $cheapestPlanId = null;
            $minPrice = PHP_INT_MAX;

            foreach ($plans as $plan) {
                $planDetails = userCurrencyAndPrice('', $plan);
                if ($planDetails === []) {
                    continue;
                }

                if (is_null($planDetails['plan'])) {
                    continue;
                }

                $cost = rounding($planDetails['plan']->add_price); // Get price and round it
                $currencyCode = $planDetails['currency']; // Get currency code

                // Format price without symbol
                $formattedPrice = currencyFormat($cost, $code = $currencyCode);
                $finalPrice = trim(str_replace($planDetails['symbol'], '', $formattedPrice));

                // Store formatted price
                $prices[$plan->id] = $finalPrice;

                // Track cheapest plan
                if ($cost < $minPrice) {
                    $minPrice = $cost;
                    $cheapestPlanId = $plan->id;
                }
            }

            return [
                'prices' => $prices,
                'cheapestPlanId' => $cheapestPlanId,
            ];
        } catch (Exception $exception) {
            Logger::exception($exception);

            return [
                'prices' => [],
                'cheapestPlanId' => null,
            ];
        }
    }
}
