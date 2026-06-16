<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsappMessage;
use App\Model\Common\StatusSetting;
use App\WhatsappIntegration;
use App\WhatsappIntegrationUser;
use Auth;
use Exception;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Log;
use Session;

/**
 * WhatsApp Business (Meta) integration.
 *
 * Three areas:
 *  - Admin config: store the Meta app credentials (whatsapp_integration) and list every registered number.
 *  - Client embedded-signup: a customer attaches their own WhatsApp number to an order via Meta's
 *    embedded signup, then can edit the webhook URL or deregister the number.
 *  - Webhook: Meta posts inbound messages to faveo-whatsapp; we relay them to the number's callback URL.
 */
class WhatsappController extends Controller
{
    protected $base_url;

    protected $api_version;

    protected $endpoint;

    public function __construct()
    {
        // The Meta webhook is a public callback (also CSRF-exempt) — never behind auth/admin.
        $this->middleware('auth', ['except' => ['whatsappWebhook']]);
        // Endpoints reachable by clients (acting on their own numbers / embedded-signup) are
        // exempt from the admin gate; ownership is enforced per-method below.
        $this->middleware('admin', [
            'except' => ['whatsappWebhook', 'urlSave', 'saveWabaId', 'getWebhookUrl', 'webhookUrlEdit', 'whatsappClientNumbers', 'deregister'],
        ]);

        $this->base_url = config('whatsappurl.base_url');
        $this->api_version = config('whatsappurl.api_version');
        $this->endpoint = config('whatsappurl.endpoints');
    }

    /* ───────────────────────── Admin: global config ───────────────────────── */

    /**
     * Return the stored Meta app credentials for the admin settings screen.
     */
    public function whatsappIntegration()
    {
        try {
            [$app_id, $app_secret, $config_id, $verify_token] =
                array_values(WhatsappIntegration::first()?->only(['app_id', 'app_secret', 'config_id', 'verify_token']) ?? [null, null, null, null]);

            return successResponse('', [
                'app_id' => $app_id,
                'app_secret' => $app_secret,
                'config_id' => $config_id,
                'verify_token' => $verify_token,
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Save the Meta app credentials and enable the global WhatsApp toggle.
     */
    public function whatsappSave(Request $request)
    {
        try {
            WhatsappIntegration::updateOrCreate(
                ['id' => 1],
                $request->only(['app_id', 'app_secret', 'config_id', 'verify_token'])
            );
            StatusSetting::where('id', 1)->update(['whatsapp_status' => 1]);

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Paginated, searchable list of every registered WhatsApp number (admin users table — Vue).
     */
    public function whatsappUsersApi(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder = $request->input('sort-order', 'desc');
            $allowedSorts = ['created_at', 'phone_number', 'waba_id', 'phone_number_id', 'business_id'];
            $sortField = in_array($request->input('sort-field'), $allowedSorts, true)
                ? $request->input('sort-field')
                : 'created_at';

            $users = WhatsappIntegrationUser::with('user:id,first_name,last_name,email')
                ->when($searchString, function ($query) use ($searchString): void {
                    $query->where(function (Builder $q) use ($searchString): void {
                        $q->where('phone_number', 'like', "%{$searchString}%")
                            ->orWhere('waba_id', 'like', "%{$searchString}%")
                            ->orWhere('phone_number_id', 'like', "%{$searchString}%")
                            ->orWhere('business_id', 'like', "%{$searchString}%")
                            ->orWhereHas('user', function (Builder $userQuery) use ($searchString): void {
                                $userQuery->where('first_name', 'like', "%{$searchString}%")
                                    ->orWhere('last_name', 'like', "%{$searchString}%")
                                    ->orWhere('email', 'like', "%{$searchString}%");
                            });
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($request->input('limit', 10));

            $users->getCollection()->transform(function ($model) {
                $name = trim(($model->user->first_name ?? '').' '.($model->user->last_name ?? ''));

                return [
                    'id' => $model->id,
                    'user_id' => $model->user_id,
                    'user_name' => $name ?: ($model->user->email ?? '---'),
                    'user_email' => $model->user->email ?? '',
                    'phone_number' => $model->phone_number,
                    'waba_id' => $model->waba_id,
                    'phone_number_id' => $model->phone_number_id,
                    'business_id' => $model->business_id,
                    'callback_url' => $model->user_callback_url,
                    'created_at' => $model->created_at?->format('Y-m-d H:i'),
                ];
            });

            return successResponse('', $users);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /* ──────────────────── Client: numbers for one order ───────────────────── */

    /**
     * Paginated list of the WhatsApp numbers the authenticated user has registered for one of
     * their orders. Response shape matches the client DataTable contract (paginate + successResponse).
     */
    public function whatsappClientNumbers(Request $request, $orderid)
    {
        try {
            $query = WhatsappIntegrationUser::where('user_id', Auth::id())
                ->where('order_id', $orderid);

            if ($search = trim((string) $request->input('search-query', ''))) {
                $query->where(function ($q) use ($search): void {
                    $q->where('phone_number', 'like', "%{$search}%")
                        ->orWhere('waba_id', 'like', "%{$search}%")
                        ->orWhere('phone_number_id', 'like', "%{$search}%")
                        ->orWhere('business_id', 'like', "%{$search}%");
                });
            }

            $allowed = ['phone_number', 'waba_id', 'business_id', 'created_at'];
            $sortField = in_array($request->input('sort-field'), $allowed, true)
                ? $request->input('sort-field')
                : 'created_at';
            $sortOrder = $request->input('sort-order', 'desc') === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortField, $sortOrder);

            $paginated = $query->paginate((int) $request->input('limit', 10));

            $paginated->getCollection()->transform(fn ($model) => [
                'id' => $model->id,
                'phone_number' => $model->phone_number,
                'waba_id' => $model->waba_id,
                'phone_number_id' => $model->phone_number_id,
                'business_id' => $model->business_id,
                'callback_url' => $model->user_callback_url,
                'created_at' => $model->created_at?->format('Y-m-d H:i'),
            ]);

            return successResponse('', $paginated);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /* ─────────────── Client: Meta embedded-signup (add a number) ───────────── */

    /**
     * Stash the callback URL in the session before launching Meta's embedded signup.
     */
    public function urlSave(Request $request)
    {
        Session::put('whatsapp_url', $request->input('url'));

        return successResponse('success');
    }

    /**
     * Persist the number Meta returned from embedded signup, exchange the auth code for a
     * long-lived token, resolve the display number, and subscribe our app to the WABA.
     */
    public function saveWabaId(Request $request)
    {
        try {
            $wabaId = $request->input('waba_id');
            $phoneNumberId = $request->input('phone_number_id') ?: '';
            $accessToken = $this->getToken($request->input('code'));

            WhatsappIntegrationUser::create([
                'user_id' => Auth::id(),
                'waba_id' => $wabaId,
                'phone_number_id' => $phoneNumberId,
                'business_id' => $request->input('business_id'),
                'user_callback_url' => Session::get('whatsapp_url'),
                'access_token' => $accessToken,
                'order_id' => $request->input('order_id'),
                'phone_number' => $this->getNumber($phoneNumberId, $accessToken),
            ]);
            Session::forget('whatsapp_url');

            Http::withToken($accessToken)
                ->post("https://graph.facebook.com/v17.0/{$wabaId}/subscribed_apps");

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /* ──────────────── Shared: read / edit / deregister a number ────────────── */

    /**
     * Return the stored callback URL for a number (owner or admin only).
     */
    public function getWebhookUrl(Request $request)
    {
        $record = WhatsappIntegrationUser::findOrFail($request->input('id'));

        if (! $this->canManage($record)) {
            return errorResponse(__('message.unauthorized_action'), 403);
        }

        return successResponse('url', ['url' => $record->user_callback_url, 'id' => $record->id]);
    }

    /**
     * Update the callback URL for a number (owner or admin only).
     */
    public function webhookUrlEdit(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'exists:whatsapp_integration_user,id'],
            'url' => ['required', 'string'],
        ]);

        $record = WhatsappIntegrationUser::findOrFail($request->input('id'));

        if (! $this->canManage($record)) {
            return errorResponse(__('message.unauthorized_action'), 403);
        }

        try {
            $record->update(['user_callback_url' => $request->input('url')]);

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * Deregister a number from Meta and delete it locally (owner or admin only).
     * The local row is removed even if the Meta call fails, so the slot is always freed.
     */
    public function deregister(Request $request)
    {
        $record = WhatsappIntegrationUser::findOrFail($request->input('id'));

        if (! $this->canManage($record)) {
            return errorResponse(__('message.unauthorized_action'), 403);
        }

        try {
            $url = $this->base_url.'/'.$this->api_version.'/'.$record->phone_number_id.'/'.$this->endpoint['deregister'];
            Http::post($url, ['access_token' => $record->access_token]);
            $record->delete();

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception) {
            $record->delete();

            return errorResponse(__('message.whatsapp_deregister_meta_failed'));
        }
    }

    /* ──────────────────────────── Meta webhook ─────────────────────────────── */

    /**
     * Meta webhook endpoint (faveo-whatsapp). GET verifies the subscription;
     * POST queues inbound messages for relay to the number's callback URL.
     */
    public function whatsappWebhook(Request $request)
    {
        try {
            if ($request->isMethod('get')) {
                $verifyToken = WhatsappIntegration::value('verify_token');

                if ($request->query('hub_mode') === 'subscribe' && $request->query('hub_verify_token') === $verifyToken) {
                    return response($request->query('hub_challenge'), 200);
                }

                return response('Forbidden', 403);
            }

            if ($request->isMethod('post')) {
                $rawBody = $request->getContent();
                $change = json_decode($rawBody, true)['entry'][0]['changes'][0]['value'] ?? [];

                if (isset($change['statuses'])) {
                    return response()->json(['ignored' => 'status update'], 200);
                }

                if (! isset($change['messages'])) {
                    return response()->json(['ignored' => 'not a message'], 200);
                }

                dispatch(new SendWhatsappMessage($rawBody))->onQueue('whatsapp');

                return response('EVENT_RECEIVED', 200);
            }

            return response('Method Not Allowed', 405);
        } catch (Exception $exception) {
            Log::debug('whatsappWebhook', [$exception->getMessage()]);
        }
    }

    /* ──────────────────────────── Internals ────────────────────────────────── */

    /**
     * Whether the current user may manage this number (its owner, or an admin).
     */
    private function canManage(WhatsappIntegrationUser $record): bool
    {
        $user = Auth::user();

        return $user->role === 'admin' || (int) $record->user_id === (int) $user->id;
    }

    /**
     * Resolve the display phone number for a phone_number_id from the Meta Graph API.
     */
    private function getNumber($phoneNumberId, $accessToken): string
    {
        if (! $phoneNumberId) {
            return '';
        }

        $response = Http::get($this->base_url.'/'.$this->api_version.'/'.$phoneNumberId, [
            'fields' => 'display_phone_number',
            'access_token' => $accessToken,
        ]);

        return $response->json()['display_phone_number'] ?? '';
    }

    /**
     * Exchange a Meta auth code for a long-lived access token.
     */
    private function getToken($code): string
    {
        [$app_id, $app_secret] = array_values(
            WhatsappIntegration::select(['app_id', 'app_secret'])->first()->toArray()
        );

        $url = $this->base_url.'/'.$this->api_version.'/'.$this->endpoint['access_token'];

        $shortLived = Http::get($url, [
            'client_id' => $app_id,
            'client_secret' => $app_secret,
            'code' => $code,
        ])->json()['access_token'] ?? null;

        return Http::get($url, [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $app_id,
            'client_secret' => $app_secret,
            'fb_exchange_token' => $shortLived,
        ])->json()['access_token'] ?? '';
    }
}
