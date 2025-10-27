<?php

namespace App\Http\Controllers;

use App\WhatsappIntegration;
use App\WhatsappIntegrationUser;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

Class WhatsappController extends Controller{

    protected $client;

    public function __construct(){
        $this->client= new Client();
    }

    public function index(){
        return view('themes.default1.common.whatsapp-testing');
    }

//    public function enterToken(Request $request){
//        [$app_id,$app_secret,$config_id,$version]=array_values(WhatsappIntegration::select('app_id','app_secret','config_id')->first()->toArray());
//
//        return view('themes.default1.common.whatsapp-index',compact('app_id','app_secret','config_id'));
//
//    }

    public function saveAccessToken(Request $request){
        try {
            [$app_id, $app_secret] = array_values(WhatsappIntegration::select(['app_id', 'app_secret'])->first()->toArray());
            //To get the Token
            $code = $request->input('code');

            $response = Http::get('https://graph.facebook.com/v21.0/oauth/access_token', [
                'client_id' => $app_id,      // same as client_id
                'client_secret' => $app_secret,  // same as client_secret
                'code' => $code,
            ]);

            $content = $response->json();


            //Exchange the token to get permanent token
            $access_token = $content['access_token'];

            $getToken = Http::get('https://graph.facebook.com/v21.0/oauth/access_token', [
                'query' => [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => '802421802601961',
                    'client_secret' => 'ffee9f421aa0ce1a7c79d87bd4931206',
                    'fb_exchange_token' => $access_token,
                ]
            ]);

            $content = $getToken->json();

            WhatsappIntegrationUser::updateOrCreate(['user_id' => \Auth::user()->id], ['access_token' => $content['access_token'],'user_id'=>\Auth::user()->id]);
            return successResponse(__('message.updated-successfully'));
        }catch (\Exception $exception){
            return errorResponse($exception->getMessage());
        }
    }


    public function saveWabaId(Request $request){
        try {
            $wabaId = $request->input('wabaId');
            $phoneNumberId = $request->input('phoneNumberId');
            $phoneNumber = $request->input('phoneNumber');
            WhatsappIntegrationUser::create(['user_id' => \Auth::user()->id, 'waba_id' => $wabaId,
                            'phone_number_id' => $phoneNumberId, 'phone_number' => $phoneNumber]);
            return successResponse(__('message.updated-successfully'));
        }catch (\Exception $exception){
            return errorResponse($exception->getMessage());

        }
    }

    public function whatsappWebhook(Request $request){
        // Handle GET request (Verification)
        if ($request->isMethod('get')) {
            $verify_token = WhatsappIntegration::select('verify_token')->first(); // must match your token in Meta dashboard

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

            if(!empty($data['entry'][0]['id'])) {
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

}