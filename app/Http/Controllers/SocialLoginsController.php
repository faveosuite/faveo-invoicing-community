<?php

namespace App\Http\Controllers;

use App\SocialLogin;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SocialLoginsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function getSocialLogin(Request $request): JsonResponse
    {
        $search = $request->input('search-query', '');
        $sortField = $request->input('sort-field', 'created_at');
        $sortOrder = $request->input('sort-order', 'desc');
        $limit = $request->input('limit', 10);

        $query = SocialLogin::select('id', 'type', 'client_id', 'client_secret', 'redirect_url', 'status')
            ->when($search, function ($q) use ($search): void {
                $q->where('type', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('client_id', 'like', sprintf('%%%s%%', $search));
            });

        $socialLogins = $query->orderBy($sortField, $sortOrder)
            ->paginate($limit);

        return successResponse('', $socialLogins);
    }

    public function editSocialLogin(mixed $id): JsonResponse
    {
        $socialLogins = SocialLogin::where('id', $id)->first();

        return successResponse('', $socialLogins);
    }

    public function updateSocialLogin(Request $request): JsonResponse
    {
        // Real credentials are only needed to actually turn the login ON —
        // saving blank is always allowed while it stays Inactive, so admins
        // can clear a mistaken value back out (see QA bug #33).
        $isTogglingActive = (int) $request->input('optradio') === 1;

        $request->validate([
            'client_id' => [Rule::requiredIf($isTogglingActive && in_array($request->input('type'), ['Google', 'Github', 'Linkedin'], true))],
            'client_secret' => [Rule::requiredIf($isTogglingActive && in_array($request->input('type'), ['Google', 'Github', 'Linkedin'], true))],
            'api_key' => [Rule::requiredIf($isTogglingActive && $request->input('type') === 'Twitter')],
            'api_secret' => [Rule::requiredIf($isTogglingActive && $request->input('type') === 'Twitter')],
            'redirect_url' => [Rule::requiredIf($isTogglingActive)],
        ],
            [
                // Rule::requiredIf() compiles down to the plain "required" rule at
                // validation time (not "required_if"), so the message key must match that.
                'client_id.required' => __('validation.social_login.client_id_required'),
                'client_secret.required' => __('validation.social_login.client_secret_required'),
                'api_key.required' => __('validation.social_login.api_key_required'),
                'api_secret.required' => __('validation.social_login.api_secret_required'),
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
        } catch (Exception) {
            return errorResponse(__('message.error_occurred_social_login'));
        }
    }
}
