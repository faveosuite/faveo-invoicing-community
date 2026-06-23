<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use App\Model\Github\Github;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GithubController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Validate and persist GitHub credentials + integration status.
     */
    public function postSettings(Request $request): JsonResponse
    {
        try {
            $username = $request->input('git_username');
            $token = $request->input('git_password');

            if (! GithubApiController::validateCredentials($username, $token)) {
                return errorResponse(__('message.github_invalid'));
            }

            StatusSetting::where('id', 1)->update(['github_status' => $request->input('status')]);
            Github::where('id', 1)->update(['username' => $username, 'password' => $token]);

            return successResponse(__('message.github_valid'));
        } catch (Exception) {
            return errorResponse(__('message.github_invalid'));
        }
    }

}
