<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Model\Common\Country;
use App\Model\Common\Setting;
use App\Model\Payment\Currency;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * @var Currency
     */
    public $currency;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $currency = new Currency;
        $this->currency = $currency;
    }

    /**
     * Get Currency List.
     */
    public function getCurrencyList(Request $request): JsonResponse
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortField = $request->input('sort-field', 'id');
            $sortOrder = $request->input('sort-order', 'desc');
            $limit = $request->input('limit', 10);

            // Get default currency
            $defaultCurrency = Setting::value('default_currency');

            // Query for currencies (include default currency so it can be shown with is_default flag)
            $currencyData = Currency::whereNotNull('name')
                ->whereIn('id', function ($subQuery): void {
                    $subQuery->selectRaw('MIN(id)')
                        ->from('currencies')
                        ->whereNotNull('name')
                        ->groupBy('name', 'code');
                })
                ->when($searchString, function ($q) use ($searchString): void {
                    $q->where(function ($inner) use ($searchString): void {
                        $inner->where('name', 'like', sprintf('%%%s%%', $searchString))
                            ->orWhere('code', 'like', sprintf('%%%s%%', $searchString))
                            ->orWhere('symbol', 'like', sprintf('%%%s%%', $searchString));
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->paginate($limit);

            // Map data for JSON response
            $currencyData->getCollection()->transform(fn ($currency): array => [
                'id' => $currency->id,
                'name' => $currency->name,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'status' => (bool) $currency->status,
                'is_default' => $currency->code === $defaultCurrency,
                'dashboard_currency' => (bool) $currency->dashboard_currency,
            ]);

            return successResponse(__('message.currency_list_retrieved_successfully'), $currencyData);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage(), 500);
        }
    }

    /**
     * Activate the Currency to be Shown on Dashboard.
     */
    public function setDashboardCurrency(mixed $id): JsonResponse
    {
        Currency::where('id', $id)->update(['dashboard_currency' => 1]);
        $dashboardStatus = Currency::where('id', '!=', $id)->select('dashboard_currency', 'id')->get();
        foreach ($dashboardStatus as $status) {
            $status = Currency::where('id', $status->id)->update(['dashboard_currency' => 0]);
        }

        return successResponse(__('message.updated-successfully'));
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
    public function edit($id): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $nicename = Country::where('country_id', $request->editnicename)->value('country_name');
            $codeChar2 = Country::where('country_id', $request->editnicename)->value('country_code_char2');
            $currency = Currency::where('id', $request->currencyId)->first();
            if (is_null($currency)) {
                return errorResponse('Currency not found.');
            }
            /** @var mixed $currency */
            $currency->code = $request->editcode;
            $currency->symbol = $request->editsymbol;
            $currency->name = $request->editcurrency_name;
            $currency->base_conversion = '1.0';
            $currency->country_code_char2 = $codeChar2;
            $currency->nicename = $nicename;
            $currency->save();

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updatecurrency(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request): JsonResponse {
                $currency = Currency::findOrFail($request->input('current_id'));
                if (! $currency instanceof Currency) {
                    throw new Exception('Currency not found.');
                }

                $newStatus = $request->input('current_status') == '1' ? 0 : 1;

                $currency->update(['status' => $newStatus]);

                return successResponse(__('message.updated-successfully'), [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'status' => $currency->status,
                ]);
            });
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function setDefaultCurrency(mixed $id): JsonResponse
    {
        try {
            $currency = Currency::findOrFail($id);
            if (! $currency instanceof Currency) {
                throw new Exception('Currency not found.');
            }
            Setting::where('id', 1)->update([
                'default_currency' => $currency->code,
                'default_symbol' => $currency->symbol,
            ]);

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
