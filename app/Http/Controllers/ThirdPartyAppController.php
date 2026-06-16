<?php

namespace App\Http\Controllers;

use App\ThirdPartyApp;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ThirdPartyAppController extends Controller
{
    private $thirdParty;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');

        $thirdParty = new ThirdPartyApp();
        $this->thirdParty = $thirdParty;
    }

    /*
    * Get All the third party apps
    */
    public function getThirdPartyDetails(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'asc');
            $sortField = $request->input('sort-field', 'created_at');
            $limit = $request->input('limit', 10);

            $query = $this->thirdParty
                ->select('id', 'app_name', 'app_key', 'app_secret')
                ->when($searchString, function ($q) use ($searchString): void {
                    $q->where(function ($sub) use ($searchString): void {
                        $sub->where('app_name', 'like', "%{$searchString}%")
                            ->orWhere('app_key', 'like', "%{$searchString}%");
                    });
                });

            $total = $query->count();

            $thirdPartyApps = $query->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            return successResponse(__('message.third_party_apps_fetched'), [
                'third_party_apps' => $thirdPartyApps,
                'total' => $total,
            ]);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function createThirdPartyApp(Request $request)
    {
        $this->validate($request, [
            'app_name' => 'required',
            'app_key' => 'required|size:32',
            'app_secret' => 'required',
        ],
            [
                'app_name.required' => __('validation.thirdparty_api.app_name_required'),
                'app_key.required' => __('validation.thirdparty_api.app_key_required'),
                'app_key.size' => __('validation.thirdparty_api.app_key_size'),
                'app_secret.required' => __('validation.thirdparty_api.app_secret_required'),
            ]);
        $this->thirdParty->fill($request->all())->save();

        return successResponse(__('message.saved-successfully'));
    }

    public function getAppKey()
    {
        try {
            $code = Str::random(32);
            echo $code;
        } catch (Exception $ex) {
            return back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  ThirdPartyApp  $thirdPartyApp
     * @return Response
     */
    public function updateThirdPartyApp(Request $request, $id)
    {
        $this->validate($request, [
            'app_name' => 'required',
            'app_key' => 'required|size:32',
            'app_secret' => 'nullable|string',
        ], [
            'app_name.required' => __('validation.thirdparty_api.app_name_required'),
            'app_key.required' => __('validation.thirdparty_api.app_key_required'),
            'app_key.size' => __('validation.thirdparty_api.app_key_size'),
        ]);

        $thirdPartyApp = ThirdPartyApp::findOrFail($id);

        $data = $request->only(['app_name', 'app_key']);
        if ($request->filled('app_secret')) {
            $data['app_secret'] = $request->app_secret;
        }

        $thirdPartyApp->update($data);

        return successResponse(__('message.updated-successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ThirdPartyApp  $thirdPartyApp
     * @return Response
     */
    public function deleteThirdPartyApp(Request $request)
    {
        try {
            $ids = $request->input('select');

            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            $ids = array_filter(array_map(trim(...), $ids));

            if (! is_array($ids) || empty($ids)) {
                return errorResponse(__('message.select-a-row'));
            }

            $deleted = [];
            $notFound = [];

            foreach ($ids as $id) {
                $app = $this->thirdParty->where('id', $id)->first();

                if ($app) {
                    $app->delete();
                    $deleted[] = $id;
                } else {
                    $notFound[] = $id;
                }
            }

            if (! empty($notFound)) {
                return errorResponse(__('message.no-record'));
            }

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }
}
