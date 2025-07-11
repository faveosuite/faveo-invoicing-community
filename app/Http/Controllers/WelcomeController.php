<?php

namespace App\Http\Controllers;

use App\Model\Common\Country;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class WelcomeController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->middleware('auth', ['except' => ['getCode']]);
        $this->request = $request;
    }

    public function getCode()
    {
        $code = '';
        $country = new Country();
        $country_iso2 = $this->request->get('country_id');
        $model = $country->where('country_code_char2', $country_iso2)->select('phonecode')->first();
        if ($model) {
            $code = $model->phonecode;
        }

        return $code;
    }

    public function getCurrency()
    {
        $currency = 'INR';
        $country_iso2 = $this->request->get('country_id');
        if ($country_iso2 != 'IN') {
            $currency = 'USD';
        }

        return $currency;
    }

    public function getCountry()
    {
        return view('themes.default1.common.country-count');
    }

    public function countryCount()
    {
        $users = Country::query()
            ->select('country_name', 'country_code_char2 as code')
            ->withCount('users');

        return DataTables::of($users)
            ->addColumn('country', function ($model) {
                return ucfirst($model->country_name);
            })
            ->addColumn('count', function ($model) {
                return '<a href="' . url('clients?country='.$model->code) . '">'
                    . $model->users_count . '</a>';
            })
            ->orderColumn('count', 'users_count $1')
            ->filterColumn('country', function ($query, $keyword) {
                $query->where('country_name', 'like', "%{$keyword}%");
            })
            ->rawColumns(['count'])
            ->make(true);
    }
}
