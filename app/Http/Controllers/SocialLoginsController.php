<?php

namespace App\Http\Controllers;

use App\SocialLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SocialLoginsController extends Controller
{
    public function view()
    {
        $socialLoginss = SocialLogin::get();

        return view('themes.default1.common.socialLogins', compact('socialLoginss'));
    }

    public function edit($id)
    {
        $socialLogins = SocialLogin::where('id', $id)->first();

        return view('themes.default1.common.editSocialLogins', compact('socialLogins'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'client_id' => 'required_if:type,Google,Github,Linkedin',
            'client_secret' => 'required_if:type,Google,Github,Linkedin',
            'api_key' => 'required_if:type,Twitter',
            'api_secret' => 'required_if:type,Twitter',
            'redirect_url' => 'required',
            ],
            [
                'client_id.required_if'     => __('validation.social_login.client_id_required'),
                'client_secret.required_if' => __('validation.social_login.client_secret_required'),
                'api_key.required_if'       => __('validation.social_login.api_key_required'),
                'api_secret.required_if'    => __('validation.social_login.api_secret_required'),
                'redirect_url.required'     => __('validation.social_login.redirect_url_required'),
        ]);

        try {
            SocialLogin::where('type', $request->type)->update([
                'client_id' => $request->type === 'Twitter' ? $request->api_key : $request->client_id,
                'client_secret' => $request->type === 'Twitter' ? $request->api_secret : $request->client_secret,
                'redirect_url' => $request->redirect_url,
                'status' => $request->optradio,
            ]);

            Session::flash('success', __('message.social_login_settings_updated'));
        } catch (\Exception $e) {
            Session::flash('error', __('message.error_occurred_social_login'));
        }

        return redirect()->back();
    }
}
