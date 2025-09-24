<?php

namespace App\Http\Controllers;

use App\SocialLogin;
use Illuminate\Http\Request;

class SocialLoginsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function getSocialLogin(Request $request)
    {
        $search = $request->input('search-query', '');
        $sortField = $request->input('sort-field', 'created_at');
        $sortOrder = $request->input('sort-order', 'desc');
        $limit = $request->input('limit', 10);

        $query = SocialLogin::select('id', 'type', 'client_id', 'client_secret', 'redirect_url', 'status')
                 ->when($search, function ($q) use ($search) {
                     $q->where('type', 'like', "%{$search}%")
                       ->orWhere('client_id', 'like', "%{$search}%");
                 });

        $socialLogins = $query->orderBy($sortField, $sortOrder)
                              ->simplePaginate($limit);

        return successResponse('', $socialLogins);
    }

    public function editSocialLogin($id)
    {
        $socialLogins = SocialLogin::where('id', $id)->first();

        return successResponse('', $socialLogins);
    }

    public function updateSocialLogin(Request $request)
    {
        $request->validate([
            'client_id' => 'required_if:type,Google,Github,Linkedin',
            'client_secret' => 'required_if:type,Google,Github,Linkedin',
            'api_key' => 'required_if:type,Twitter',
            'api_secret' => 'required_if:type,Twitter',
            'redirect_url' => 'required',
        ],
            [
                'client_id.required_if' => __('validation.social_login.client_id_required'),
                'client_secret.required_if' => __('validation.social_login.client_secret_required'),
                'api_key.required_if' => __('validation.social_login.api_key_required'),
                'api_secret.required_if' => __('validation.social_login.api_secret_required'),
                'redirect_url.required' => __('validation.social_login.redirect_url_required'),
            ]);

        try {
            SocialLogin::where('type', $request->type)->update([
                'client_id' => $request->type === 'Twitter' ? $request->api_key : $request->client_id,
                'client_secret' => $request->type === 'Twitter' ? $request->api_secret : $request->client_secret,
                'redirect_url' => $request->redirect_url,
                'status' => $request->optradio,
            ]);

            return successResponse(__('message.social_login_settings_updated'));
        } catch (\Exception $e) {
            return errorResponse(__('message.error_occurred_social_login'));
        }
    }
}
