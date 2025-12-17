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

        return successResponse('code', ['code' => $code]);
    }

    public function getCurrency()
    {
        $currency = 'INR';
        $country_iso2 = $this->request->get('country_id');
        if ($country_iso2 != 'IN') {
            $currency = 'USD';
        }

        return successResponse('currency', ['Currency' => $currency]);
    }

    /**
     * Get country list with user count.
     */
    public function getCountry(Request $request)
    {
        try {
            $searchQuery = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'country_name');
            $limit = $request->input('limit', 10);

            $countryList = Country::withCount('users')
                ->where('country_name', '!=', '')
                ->when($searchQuery, function ($query, $searchQuery) {
                    $query->where('country_name', 'like', "%{$searchQuery}%");
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $countryList->getCollection()->transform(function ($country) {
                return [
                    'id' => $country->country_id,
                    'country' => ucfirst($country->country_name ?? ''),
                    'code' => $country->country_code_char2 ?? '',
                    'count' => $country->users_count ?? 0,
                ];
            });

            return successResponse('', $countryList);
        } catch (\Exception $e) {
            return errorResponse(__('message.something_went_wrong'), 500);
        }
    }
}
