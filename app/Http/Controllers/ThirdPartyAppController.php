<?php

namespace App\Http\Controllers;

use App\ThirdPartyApp;
use Illuminate\Http\Request;
use function Laravel\Prompts\error;

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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
//    public function index()
//    {
//        return view('themes.default1.third-party.index');
//    }

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
                ->when($searchString, function ($q) use ($searchString) {
                    $q->where(function ($sub) use ($searchString) {
                        $sub->where('app_name', 'like', "%{$searchString}%")
                            ->orWhere('app_key', 'like', "%{$searchString}%");
                    });
                });

            if ($sortOrder != '' && $sortField != '') {
                $query->orderBy($sortField, $sortOrder);
            }

            $thirdPartyApps = $query->simplePaginate($limit)->toArray();

            foreach ($thirdPartyApps['data'] as &$app) {
                $app['app_secret'] = '*****';
            }

            $total = $query->count();


            return successResponse( __('message.third_party_apps_fetched'), [
                'third_party_apps' => $thirdPartyApps,
                'total' => $total
            ]);

        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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

        return successResponse( __('message.saved-successfully'));
    }

    public function getAppKey()
    {
        try {
            $code = str_random(32);
            echo $code;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ThirdPartyApp  $thirdPartyApp
     * @return \Illuminate\Http\Response
     */
    public function show(ThirdPartyApp $thirdPartyApp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ThirdPartyApp  $thirdPartyApp
     * @return \Illuminate\Http\Response
     */
    public function edit(ThirdPartyApp $thirdPartyApp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ThirdPartyApp  $thirdPartyApp
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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

        $thirdPartyApp = ThirdPartyApp::findOrFail($id);

        $thirdPartyApp->update($request->only(['app_name', 'app_key', 'app_secret']));

        return redirect()->back()->with('success', __('message.updated-successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ThirdPartyApp  $thirdPartyApp
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');

            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            $ids = array_filter(array_map('trim', $ids));

            if (!is_array($ids) || empty($ids)) {
                return errorResponse( __('message.select-a-row'));
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

            if (!empty($notFound)) {
                return errorResponse( __('message.no-record'));
            }

            return successResponse( __('message.deleted-successfully'));

        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }
}
