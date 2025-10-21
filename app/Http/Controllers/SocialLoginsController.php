<?php

namespace App\Http\Controllers;

use App\SocialLogin;
use Illuminate\Http\Request;

class SocialLoginsController extends Controller
{
    public function view()
    {
        $socialLoginss = SocialLogin::get();

        return successResponse('', $socialLoginss);
    }

    public function edit($id)
    {
        $socialLogins = SocialLogin::where('id', $id)->first();

        return successResponse('', $socialLogins);
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|string|in:Google,Github,Linkedin,Twitter',
                'client_id' => 'required_if:type,Google,Github,Linkedin',
                'client_secret' => 'required_if:type,Google,Github,Linkedin',
                'api_key' => 'required_if:type,Twitter',
                'api_secret' => 'required_if:type,Twitter',
                'redirect_url' => 'required|url',
                'optradio' => 'nullable|in:1,0', // active/inactive
            ], [
                'client_id.required_if' => __('validation.social_login.client_id_required'),
                'client_secret.required_if' => __('validation.social_login.client_secret_required'),
                'api_key.required_if' => __('validation.social_login.api_key_required'),
                'api_secret.required_if' => __('validation.social_login.api_secret_required'),
                'redirect_url.required' => __('validation.social_login.redirect_url_required'),
                'redirect_url.url' => __('validation.social_login.redirect_url_invalid'),
            ]);

            $updated = SocialLogin::where('type', $validated['type'])->update([
                'client_id' => $validated['type'] === 'Twitter' ? $validated['api_key'] : $validated['client_id'],
                'client_secret' => $validated['type'] === 'Twitter' ? $validated['api_secret'] : $validated['client_secret'],
                'redirect_url' => $validated['redirect_url'],
                'status' => $validated['optradio'] ?? 0,
            ]);

            if ($updated) {
                return successResponse(__('message.social_login_settings_updated'));
            }

            return errorResponse(__('message.no-record'), [], 404);
        } catch (\Exception $e) {
            return errorResponse(__('message.error_occurred_social_login').': '.$e->getMessage());
        }
    }
}
