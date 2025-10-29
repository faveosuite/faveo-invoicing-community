<?php

namespace App\Http\Controllers;

use App\User;
use App\WhatsappIntegration;
use App\WhatsappIntegrationUser;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function index()
    {
        [$app_id, $config_id] =
            array_values(WhatsappIntegration::first()?->only(['app_id', 'config_id']) ?? [null, null, null, null, null]);

        return view('themes.default1.common.whatsapp-testing', compact('app_id', 'config_id'));
    }

//    public function enterToken(Request $request){
//        [$app_id,$app_secret,$config_id,$version]=array_values(WhatsappIntegration::select('app_id','app_secret','config_id')->first()->toArray());
//
//        return view('themes.default1.common.whatsapp-index',compact('app_id','app_secret','config_id'));
//
//    }

    public function index1()
    {
        return view('themes.default1.common.whatsapp-index');
    }

    public function whatsappTable()
    {
        $query = WhatsappIntegrationUser::select('*');

        return \DataTables::of($query)
            ->orderColumn('UserName', '-created_at $1')
            ->orderColumn('PhoneNumber', '-created_at $1')
            ->orderColumn('WabaId', '-created_at $1')
            ->orderColumn('PhoneNumberId', '-created_at $1')
            ->orderColumn('BusinessId', '-created_at $1')
            ->addColumn('UserName', function ($model) {
                $user = User::select('first_name', 'last_name')->find($model->user_id);

                return $user ? "{$user->first_name} {$user->last_name}" : '';
            })
            ->addColumn('PhoneNumber', function ($model) {
                return $model->phone_number;
            })
            ->addColumn('WabaId', function ($model) {
                return $model->waba_id;
            })
            ->addColumn('PhoneNumberId', function ($model) {
                return $model->phone_number_id;
            })
            ->addColumn('BusinessId', function ($model) {
                return $model->business_id;
            })
            ->addColumn('created_at', function ($model) {
                return  getDateHtml($model->created_at);
            })

            ->rawColumns(['UserName', 'PhoneNumber', 'WabaId', 'PhoneNumberId', 'BusinessId', 'created_at'])
            ->make(true);
    }

    public function saveAccessToken(Request $request)
    {
        try {
            [$app_id, $app_secret] = array_values(WhatsappIntegration::select(['app_id', 'app_secret'])->first()->toArray());
            //To get the Token
            $code = $request->input('code');

            $response = Http::get('https://graph.facebook.com/v21.0/oauth/access_token', [
                'client_id' => $app_id,
                'client_secret' => $app_secret,
                'code' => $code,
            ]);

            $content = $response->json();

            //Exchange the token to get permanent token
            $access_token = $content['access_token'];

            $getToken = Http::get('https://graph.facebook.com/v21.0/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $app_id,
                'client_secret' => $app_secret,
                'fb_exchange_token' => $access_token,
            ]);

            $content = $getToken->json();

            WhatsappIntegrationUser::updateOrCreate(['user_id' => \Auth::user()->id], ['access_token' => $content['access_token'], 'user_id' => \Auth::user()->id]);
            $this->saveNumber($content['access_token']);

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function saveNumber($access_token)
    {
        $phone_number_id = WhatsappIntegrationUser::where('user_id', \Auth::user()->id)->value('phone_number_id');
        if ($phone_number_id) {
            $data = Http::get("https://graph.facebook.com/v21.0/{$phone_number_id}", [
                'fields' => 'display_phone_number',
                'access_token' => $access_token,
            ]);
            $content = $data->json();
            WhatsappIntegrationUser::where('phone_number_id', $phone_number_id)->update(['phone_number' => $content['display_phone_number']]);
        }
    }

    public function saveWabaId(Request $request)
    {
        try {
            $wabaId = $request->input('waba_id');
            $phoneNumberId = $request->input('phone_number_id') ? $request->input('phone_number_id') : '';
            $business_id = $request->input('business_id');
            WhatsappIntegrationUser::create(['user_id' => \Auth::user()->id, 'waba_id' => $wabaId,
                'phone_number_id' => $phoneNumberId, 'business_id' => $business_id]);

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function whatsappWebhook(Request $request)
    {
        // Handle GET request (Verification)
        if ($request->isMethod('get')) {
            $verify_token = WhatsappIntegration::select('verify_token')->first();

            $mode = $request->query('hub_mode');
            $token = $request->query('hub_verify_token');
            $challenge = $request->query('hub_challenge');

            if ($mode === 'subscribe' && $token === $verify_token) {
                return response($challenge, 200);
            }

            return response('Forbidden', 403);
        }
        if ($request->isMethod('post')) {
            $data = $request->all();
            \Log::debug('WhatsApp Webhook event:', [$data]);

            if (! empty($data['entry'][0]['id'])) {
                $wabaId = $data['entry'][0]['id'];
                $url = WhatsappIntegrationUser::where('waba_id', $wabaId)->value('url');
                $response = $this->client->post($url, ['json' => $data, // sends as JSON
                    'headers' => [
                        'X-Webhook-Token' => env('WEBHOOK_TOKEN', 'secret-token'),
                        'Accept' => 'application/json',
                    ]]);
            }

            return response('EVENT_RECEIVED', 200);
        }

        return response('Method Not Allowed', 405);
    }

    public function whatsappIntegration()
    {
        try {
            [$app_id, $app_secret, $config_id, $verify_token] =
                array_values(WhatsappIntegration::first()?->only(['app_id', 'app_secret', 'config_id', 'verify_token']) ?? [null, null, null, null]);

            $data = [
                'app_id' => $app_id,
                'app_secret' => $app_secret,
                'config_id' => $config_id,
                'verify_token' => $verify_token,
            ];

            return successResponse('', $data);
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function whatsappSave(Request $request)
    {
        try {
            [$app_id, $app_secret, $config_id, $verify_token] = array_values(
                $request->only(['app_id', 'app_secret', 'config_id', 'verify_token'])
            );
            WhatsappIntegration::UpdateorCreate(['id' => 1], ['app_id' => $app_id, 'app_secret' => $app_secret, 'config_id' => $config_id, 'verify_token' => $verify_token]);

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
