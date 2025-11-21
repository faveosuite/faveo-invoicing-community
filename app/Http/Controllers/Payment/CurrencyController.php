<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Model\Common\Country;
use App\Model\Common\Setting;
use App\Model\Payment\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Lang;

class CurrencyController extends Controller
{
    public $currency;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $currency = new Currency();
        $this->currency = $currency;
    }

    /**
     * Get Currency List.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrencyList(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortField = $request->input('sort-field', 'id');
            $sortOrder = $request->input('sort-order', 'desc');
            $limit = $request->input('limit', 10);

            // Get default currency
            $defaultCurrency = Setting::pluck('default_currency')->first();

            // Query for currencies
            $currencyData = Currency::whereNotNull('name')
                ->where('code', '!=', $defaultCurrency)
                ->whereIn('id', function ($subQuery) use ($defaultCurrency) {
                    $subQuery->selectRaw('MIN(id)')
                        ->from('currencies')
                        ->whereNotNull('name')
                        ->where('code', '!=', $defaultCurrency)
                        ->groupBy('name', 'code');
                })
                ->when($searchString, function ($q) use ($searchString) {
                    $q->where(function ($inner) use ($searchString) {
                        $inner->where('name', 'like', "%{$searchString}%")
                            ->orWhere('code', 'like', "%{$searchString}%")
                            ->orWhere('symbol', 'like', "%{$searchString}%");
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            // Map data for JSON response
            $currencyData->getCollection()->transform(function ($currency) use ($defaultCurrency) {
                return [
                    'id' => $currency->id,
                    'name' => $currency->name,
                    'code' => $currency->code,
                    'symbol' => $currency->symbol,
                    'status' => (bool) $currency->status,
                    'is_default' => $currency->code === $defaultCurrency,
                    'dashboard_currency' => (bool) $currency->dashboard_currency,
                ];
            });
            $total = $currencyData->count();

            return successResponse(__('message.currency_list_retrieved_successfully'), [
                'currencies' => $currencyData,
                'total' => $total,
            ]);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage(), 500);
        }
    }

    /**
     * Get the Color of the button when the currency is allowed to show on dashboard.
     *
     * @param  string  $id  Currrency id
     * @return string
     */
    public function getButtonColor($id)
    {
        $defaultCurrency = Setting::pluck('default_currency')->first();
        $currencyCode = Currency::where('id', $id)->pluck('code')->first(); //If default currency is equal to the currency code then make that button as Disabled as it would always be shown on dashboard and cannot be modified
        if ($defaultCurrency == $currencyCode) {
            return  '<a class="btn btn-sm btn-warning btn-xs disabled" style="background-color:#f39c12;">&nbsp;&nbsp;'.__('message.default-currency').'</a>';
        }
        $currency = Currency::where('id', $id)->pluck('dashboard_currency')->first();
        if ($currency == 1) {
            return'<form method="post" action='.url('dashboard-currency/'.$id).'>'.'<input type="hidden" name="_token" value='.\Session::token().'>'.'
                                    <button type="submit" class="btn btn-sm btn-success btn-xs"><i class="fa fa-check" style="color:white;"></i>&nbsp;&nbsp; '.__('message.show_on_dashboard').'</button></form>';
        } else {
            return '<form method="post" action='.url('dashboard-currency/'.$id).'>'.'<input type="hidden" name="_token" value='.\Session::token().'>'.'
                                    <button type="submit" class="btn btn-sm btn-danger btn-xs"><i class="fa fa-times" style="color:white;"></i>&nbsp;&nbsp; '.__('message.show_on_dashboard').'</button></form>';
        }
    }

    /**
     * Activate the Currency to be Shown on Dashboard.
     *
     *
     * @return \Response
     */
    public function setDashboardCurrency($id)
    {
        Currency::where('id', $id)->update(['dashboard_currency' => 1]);
        $dashboardStatus = Currency::where('id', '!=', $id)->select('dashboard_currency', 'id')->get();
        foreach ($dashboardStatus as $status) {
            $status = Currency::where('id', $status->id)->update(['dashboard_currency' => 0]);
        }

        return successResponse(__('message.updated-successfully'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // $this->validate($request, [
        //     'code'            => 'required',
        //     'name'            => 'required',
        // ]);

        try {
            $nicename = Country::where('country_id', $request->name)->value('country_name');
            $codeChar2 = Country::where('country_id', $request->name)->value('country_code_char2');
            $currency = new Currency();

            $currency->code = $request->code;
            $currency->symbol = $request->symbol;
            $currency->name = $request->currency_name;
            $currency->base_conversion = '1.0';
            $currency->country_code_char2 = $codeChar2;
            $currency->nicename = $nicename;
            $currency->save();

            // $this->currency->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function update(Request $request)
    {
        try {
            $nicename = Country::where('country_id', $request->editnicename)->value('country_name');
            $codeChar2 = Country::where('country_id', $request->editnicename)->value('country_code_char2');
            $currency = Currency::where('id', $request->currencyId)->first();
            $currency->code = $request->editcode;
            $currency->symbol = $request->editsymbol;
            $currency->name = $request->editcurrency_name;
            $currency->base_conversion = '1.0';
            $currency->country_code_char2 = $codeChar2;
            $currency->nicename = $nicename;
            $currency->save();

            return response()->json(['success' => Lang::get('message.updated-successfully')]);
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
                    if ($id != 1) {
                        $currency = $this->currency->where('id', $id)->first();
                        if ($currency) {
                            $currency->delete();
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
                        './* @scrutinizer ignore-type */\Lang::get('message.can-not-delete-default').'
                </div>';
                    }
                }
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
                    /* @scrutinizer ignore-type */
                    \Lang::get('message.failed').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }

    public function countryDetails(Request $request)
    {
        $countryDetails = Country::where('country_id', $request->id)->select('currency_code', 'currency_symbol', 'currency_name')->first();
        $data = ['code' => $countryDetails->currency_code,
            'symbol' => $countryDetails->currency_symbol, 'currency' => $countryDetails->currency_name, ];

        return $data;
    }

    public function updatecurrency(Request $request)
    {
        try {
            return \DB::transaction(function () use ($request) {
                $currency = Currency::findOrFail($request->input('current_id'));

                $newStatus = $request->input('current_status') == '1' ? 0 : 1;

                if ($newStatus) {
                    Artisan::call('currency:manage', [
                        'action' => 'add',
                        'currency' => $currency->code,
                    ]);

                    Artisan::call('currency:cleanup');
                }

                $currency->update(['status' => $newStatus]);

                return successResponse(__('message.updated-successfully'), [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'status' => $currency->status,
                ]);
            });
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }
}
