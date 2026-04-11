<?php

namespace App\Modules\License\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\License\Requests\whitelistIpsRequest;
use App\Modules\License\Models\LicenseBannedHost;
use App\Modules\License\Models\LicenseWhitelistIp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

class WhitelistIpsController extends Controller
{

    public function whitelistAdd(whitelistIpsRequest $request)
    {
        try{
        $whitelist_host_ip = $request->input('whitelist_host_ip');
        $whitelist_host_comments = $request->input('whitelist_host_comments');
        $id = $request->id;
        $bannedHosts = LicenseBannedHost::pluck('banned_host_ip')->toArray();
        if(in_array($whitelist_host_ip,$bannedHosts)){
            return errorResponse($whitelist_host_ip .Lang::get('lang.already_exist_ip'), 500);
        }
            $whitelist = LicenseWhitelistIp::updateOrCreate(
                ['whitelist_host_id' => $id],
                [
                    'whitelist_host_ip' => $whitelist_host_ip,
                    'whitelist_host_comments' => $whitelist_host_comments,
                ]
            );

       $responseMessage = Lang::get('lang.whitelist_' . ($id ? 'update' : 'add'));
       $statusCode = $id ? 200 : 201;

       return successResponse($responseMessage, $whitelist, $statusCode);
    }
    catch(\Exception $e){
        return errorResponse($e,404);
    }
    }

    public function deleteWhitelistIp(Request $request)
    {
        try {
            $host_data = LicenseWhitelistIp::where('whitelist_host_id', $request->whitelist_host_id)->firstOrFail();
            $host_data->delete();
            return successResponse(Lang::get('lang.delete'), 201);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return errorResponse(Lang::get('lang.invalid'), 404);
            }
    }
    public function edit($whitelist_host_id)
    {
        $host_data = LicenseWhitelistIp::where('whitelist_host_id', $whitelist_host_id)->firstOrFail();

        if (! empty($host_data)) {
            return successResponse('data', ['host_data' => $host_data], 200);
        }

        return errorResponse(Lang::get('lang.invalid'), 400);
    }
    public function view(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        $searchQuery = $request->input('search_query');
        $sortOrder= $request->input('sort_order','desc');
        $sortField = $request->input('sort_field','whitelist_host_id');


        $records = LicenseWhitelistIp::when($searchQuery, function ($query) use ($searchQuery) {
            return $query->where('whitelist_host_ip', 'like', '%'.$searchQuery.'%')
                ->orWhere('whitelist_host_comments', 'like', '%'.$searchQuery.'%');
        })->orderBy($sortField, $sortOrder)
        ->paginate($perPage, ['*'], 'page', $page);

        $records->getCollection()->transform(function ($record) {
            return [
                'whitelist_host_id' => $record->whitelist_host_id,
                'whitelist_host_date' => $record->created_at->format('Y-m-d'), // Rename and format created_at
                'whitelist_host_ip' => $record->whitelist_host_ip,
                'whitelist_host_comments' => $record->whitelist_host_comments,
            ];
        });
        return successResponse(Lang::get('lang.view_whitelist_ip'), $records, 201);
    }

}
