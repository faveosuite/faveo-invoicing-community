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
            'client_id' => 'required',
            'client_secret' => 'required',
        ]);

        try {
            SocialLogin::where('type', $request->type)->update([
                'client_id' => $request->client_id,
                'client_secret' => $request->client_secret,
                'redirect_url' => $request->redirect_url,
                'status' => $request->optradio,
            ]);

            Session::flash('success', 'Social login settings updated successfully');
        } catch (\Exception $e) {
            Session::flash('error', 'An error occurred while updating social login settings');
        }

        return redirect()->back();
    }
}
