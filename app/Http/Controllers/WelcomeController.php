<?php

namespace App\Http\Controllers;

use App\Model\Common\Country;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getCode']]);
    }

    /**
     * Get country list with user count.
     */
    public function getCountry(Request $request): JsonResponse
    {
        try {
            $searchQuery = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'country_name');
            $limit = $request->input('limit', 10);

            $countryList = Country::withCount('users')
                ->where('country_name', '!=', '')
                ->when($searchQuery, function ($query, string $searchQuery): void {
                    $query->where('country_name', 'like', sprintf('%%%s%%', $searchQuery));
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $countryList->getCollection()->transform(fn ($country): array => [
                'id' => $country->country_id,
                'country' => ucfirst($country->country_name ?? ''),
                'code' => $country->country_code_char2 ?? '',
                'count' => $country->users_count ?? 0,
            ]);

            return successResponse('', $countryList);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong'), 500);
        }
    }
}
