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
     * Display a listing of the resource.
     *
     * @return \Response
     */
    public function index()
    {
        return view('themes.default1.payment.currency.index');
    }

    public function getCurrency()
    {
        $model = Currency::select('id', 'name', 'code', 'symbol', 'status');

        return \DataTables::of($model)
            ->editColumn('name', function ($model) {
                return e($model->name);
            })
            ->editColumn('code', function ($model) {
                return '<div class="text-center">'.e($model->code).'</div>';
            })
            ->editColumn('symbol', function ($model) {
                return '<div class="text-center">'.e($model->symbol).'</div>';
            })
            ->addColumn('dashboard', function ($model) {
                if ($model->status == 1) {
                    $showButton = $this->getButtonColor($model->id);

                    return '<div class="dashboard-center">'.$showButton.'</div>';
                } else {
                    return '<div class="dashboard-center">
                    <a class="btn btn-sm btn-secondary btn-xs disabled align-items-center" style="margin-right: 100px;">
                        <i class="fa fa-eye" style="color:white;"></i>&nbsp;&nbsp;'.__('message.show_on_dashboard').'
                    </a>
                </div>';
                }
            })
            ->editColumn('status', function ($model) {
                $defaultCurrencyCode = Setting::value('default_currency');
                $checked = $model->status == 1 ? 'checked' : '';

                // Check if default currency
                $isDefault = $defaultCurrencyCode === $model->code;

                $disabledAttr = $isDefault ? 'disabled' : '';
                $style = $isDefault ? 'opacity: 0.6; pointer-events: none;' : '';
                $title = $isDefault ? __('message.default-currency') : '';

                return <<<HTML
    <label class="switch toggle_event_editing" style="{$style}" title="{$title}">
        <input type="hidden" name="module_id" class="module_id" value="{$model->id}">
        <input type="checkbox" name="modules_settings" class="modules_settings_value" value="{$model->status}" {$checked} {$disabledAttr}>
        <span class="slider round"></span>
    </label>
HTML;
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->filterColumn('code', function ($query, $keyword) {
                $query->where('code', 'like', "%{$keyword}%");
            })
            ->filterColumn('symbol', function ($query, $keyword) {
                $query->where('symbol', 'like', "%{$keyword}%");
            })
            ->rawColumns(['code', 'symbol', 'dashboard', 'status'])
            ->make(true);
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

        return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
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
            $nicename = Country::where('country_id', $request->name)->value('nicename');
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
            $nicename = Country::where('country_id', $request->editnicename)->value('nicename');
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
        $code = Currency::where('id', $request->input('current_id'))->value('code');
        Artisan::call('currency:manage', ['action' => 'add', 'currency' => $code]);
        $updatedStatus = ($request->current_status == '1') ? 0 : 1;
        Currency::where('id', $request->current_id)->update(['status' => $updatedStatus]);

        return Lang::get('message.updated-successfully');
    }
}
