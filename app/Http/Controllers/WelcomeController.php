<?php

namespace App\Http\Controllers;

use App\Model\Common\Country;
use Illuminate\Http\Request;

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

    /**
     * Get country list with user count.
     */
    public function getCountry()
    {
        // Fetch countries with user count using relationship
        $countries = Country::withCount('users')
            ->where('nicename', '!=', '')
            ->get(['nicename', 'country_code_char2']);

        // Transform the output
        $data = $countries->map(function ($country) {
            return [
                'country' => ucfirst($country->nicename ?? 'Unknown'),
                'code' => $country->country_code_char2 ?? '',
                'count' => $country->users_count ?? 0,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}
