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
use Lang;
use Session;

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
                ->simplePaginate($limit);

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
     * Get the Color of the button when the currency is allowed to show on dashboard.
     *
     * @param  string  $id  Currrency id
     */
    public function getButtonColor(string $id): string
    {
        $defaultCurrency = Setting::value('default_currency');
        $currencyCode = Currency::where('id', $id)->value('code'); // If default currency is equal to the currency code then make that button as Disabled as it would always be shown on dashboard and cannot be modified
        if ($defaultCurrency == $currencyCode) {
            return '<a class="btn btn-sm btn-warning btn-xs disabled" style="background-color:#f39c12;">&nbsp;&nbsp;'.__('message.default-currency').'</a>';
        }

        $currency = Currency::where('id', $id)->value('dashboard_currency');
        if ($currency == 1) {
            return '<form method="post" action='.url('dashboard-currency/'.$id).'>'.'<input type="hidden" name="_token" value='.Session::token().'>'.'
                                    <button type="submit" class="btn btn-sm btn-success btn-xs"><i class="fa fa-check" style="color:white;"></i>&nbsp;&nbsp; '.__('message.show_on_dashboard').'</button></form>';
        }

        return '<form method="post" action='.url('dashboard-currency/'.$id).'>'.'<input type="hidden" name="_token" value='.Session::token().'>'.'
                                    <button type="submit" class="btn btn-sm btn-danger btn-xs"><i class="fa fa-times" style="color:white;"></i>&nbsp;&nbsp; '.__('message.show_on_dashboard').'</button></form>';
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request): mixed
    {
        // dd($request->all());
        // $this->validate($request, [
        //     'code'            => 'required',
        //     'name'            => 'required',
        // ]);

        try {
            $nicename = Country::where('country_id', $request->name)->value('country_name');
            $codeChar2 = Country::where('country_id', $request->name)->value('country_code_char2');
            $currency = new Currency;

            $currency->code = $request->code;
            $currency->symbol = $request->symbol;
            $currency->name = $request->currency_name;
            $currency->base_conversion = '1.0'; // @phpstan-ignore property.notFound
            $currency->country_code_char2 = $codeChar2; // @phpstan-ignore property.notFound
            $currency->nicename = $nicename; // @phpstan-ignore property.notFound
            $currency->save();

            // $this->currency->fill($request->input())->save();

            return back()->with('success', Lang::get('message.saved-successfully'));
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request): void
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                $alert = is_string($t = Lang::get('message.alert')) ? $t : '';
                $failed = is_string($t = Lang::get('message.failed')) ? $t : '';
                $success = is_string($t = Lang::get('message.success')) ? $t : '';
                $noRecord = is_string($t = Lang::get('message.no-record')) ? $t : '';
                $deletedSuccess = is_string($t = Lang::get('message.deleted-successfully')) ? $t : '';
                $cannotDeleteDefault = is_string($t = Lang::get('message.can-not-delete-default')) ? $t : '';

                foreach ($ids as $id) {
                    if ($id != 1) {
                        $currency = $this->currency->where('id', $id)->first();
                        if ($currency) {
                            $currency->delete();
                        } else {
                            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".$alert.'!</b> '.
                    $failed.'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$noRecord.'
                </div>';
                        }

                        echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>

                    <b>".$alert.'!</b> '.
                    $success.'

                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$deletedSuccess.'
                </div>';
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".$alert.'!</b> '.
                    $failed.'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$cannotDeleteDefault.'
                </div>';
                    }
                }
            } else {
                $alert = is_string($t = Lang::get('message.alert')) ? $t : '';
                $failed = is_string($t = Lang::get('message.failed')) ? $t : '';
                $selectRow = is_string($t = Lang::get('message.select-a-row')) ? $t : '';
                echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".$alert.'!</b> '.
                    $failed.'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$selectRow.'
                </div>';
            }
        } catch (Exception $exception) {
            $alert = is_string($t = Lang::get('message.alert')) ? $t : '';
            $failed = is_string($t = Lang::get('message.failed')) ? $t : '';
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>".$alert.'!</b> '.
                    $failed.'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$exception->getMessage().'
                </div>';
        }
    }

    /**
     * @return array<mixed>
     */
    public function countryDetails(Request $request): array
    {
        $countryDetails = Country::where('country_id', $request->id)->select('currency_code', 'currency_symbol', 'currency_name')->first();
        if (is_null($countryDetails)) {
            return ['code' => '', 'symbol' => '', 'currency' => ''];
        }
        /** @var mixed $countryDetails */

        return [
            'code' => $countryDetails->currency_code,
            'symbol' => $countryDetails->currency_symbol,
            'currency' => $countryDetails->currency_name,
        ];
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
