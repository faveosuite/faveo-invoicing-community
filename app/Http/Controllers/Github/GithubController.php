<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use App\Model\Github\Github;
use Exception;
use Illuminate\Http\Request;
use Lang;

class GithubController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Validate and persist GitHub credentials + integration status.
     */
    public function postSettings(Request $request)
    {
        try {
            $username = $request->input('git_username');
            $token = $request->input('git_password');

            if (! GithubApiController::validateCredentials($username, $token)) {
                return errorResponse(Lang::get('message.github_invalid'));
            }

            StatusSetting::find(1)->update(['github_status' => $request->input('status')]);
            Github::find(1)->update(['username' => $username, 'password' => $token]);

            return successResponse(Lang::get('message.github_valid'));
        } catch (Exception) {
            return errorResponse(Lang::get('message.github_invalid'));
        }
    }

    /**
     * Authenticate this application against the configured GitHub OAuth app.
     */
    public function authForSpecificApp()
    {
        try {
            return app(GithubApiController::class)->authorizeApp();
        } catch (Exception $ex) {
            return redirect('/')->with('fails', $ex->getMessage());
        }
    }
}
