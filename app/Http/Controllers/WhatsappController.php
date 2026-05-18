<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsappMessage;
use App\Model\Common\StatusSetting;
use App\User;
use App\WhatsappIntegration;
use App\WhatsappIntegrationUser;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    protected $client;
    protected $base_url;
    protected $api_version;

    protected $endpoint;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin', [
            'except' => ['urlSave', 'saveAccessToken', 'saveWabaId', 'getWebhookUrl', 'webhookUrlEdit', 'whatsappClientTable', 'deregister'],
        ]);

        $this->client = new Client();
        $this->base_url = config('whatsappurl.base_url');
        $this->api_version = config('whatsappurl.api_version');
        $this->endpoint = config('whatsappurl.endpoints');
    }

    public function index()
    {
        try {
            [$app_id, $config_id] =
                array_values(WhatsappIntegration::first()?->only(['app_id', 'config_id']) ?? [null, null]);

            return view('themes.default1.common.whatsapp-testing', compact('app_id', 'config_id'));
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getWebhookUrl(Request $request)
    {
        $id = $request->input('id');

        $cont = WhatsappIntegrationUser::where('id', $id)->first();
        $url = $cont->user_callback_url;
        $id = $cont->id;

        return successResponse('url', ['url' => $url, 'id' => $id]);
    }

//    public function enterToken(Request $request){
//        [$app_id,$app_secret,$config_id,$version]=array_values(WhatsappIntegration::select('app_id','app_secret','config_id')->first()->toArray());
//
//        return view('themes.default1.common.whatsapp-index',compact('app_id','app_secret','config_id'));
//
//    }

    public function index1()
    {
        $user_id = \Auth::user()->id;

        return response()
            ->view('themes.default1.common.whatsapp-index', compact('user_id'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function urlSave(Request $request)
    {
        $url = $request->input('url');
        \Session::put('whatsapp_url', $url);

        return successResponse('success');
    }

    public function whatsappTable(Request $request)
    {
        try {
            $query = WhatsappIntegrationUser::select('*')->with('user');

            return \DataTables::of($query)
                ->orderColumn('UserName', '-created_at $1')
                ->orderColumn('PhoneNumber', '-created_at $1')
                ->orderColumn('WabaId', '-created_at $1')
                ->orderColumn('PhoneNumberId', '-created_at $1')
                ->orderColumn('BusinessId', '-created_at $1')
                ->addColumn('UserName', function ($model) {
                    return '<a href='.url('clients/'.$model->user->id).'>'.ucfirst($model->user->first_name).'<a>';

//                return $user ? "{$user->first_name} {$user->last_name}" : '';
                })
                ->addColumn('PhoneNumber', function ($model) {
                    return $model->phone_number;
                })
                ->addColumn('WabaId', function ($model) {
                    return $model->waba_id;
                })
                ->addColumn('PhoneNumberId', function ($model) {
                    $token = e($model->phone_number_id); // escape for safety

                    return '
    <div class="d-flex align-items-center">
        <input type="password" class="form-control form-control-sm" 
               value="'.$token.'" readonly style="width: 60px; margin-right: 8px;" />
        <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" 
                data-token="'.$token.'">
            <i class="fas fa-copy"></i>
        </button>
        <span class="copy-msg text-success ms-2" style="display:none;">'.__('message.copied').'</span>
    </div>
';
                })
                ->addColumn('BusinessId', function ($model) {
                    return $model->business_id;
                })
                ->addColumn('created_at', function ($model) {
                    return getDateHtml($model->created_at);
                })
                ->addColumn('access_token', function ($model) {
                    return $model->access_token;
                })
                ->addColumn('action', function ($model) {
                    return "
    <div style='display:flex; gap:4px;'>
        <button 
            data-toggle='modal'
            data-id='".$model->id."'
            onclick=\"editWhatsappUser('".$model->id."')\"
            class='btn btn-sm btn-info btn-xs'>
            <i class='fa fa-edit' style='color:white;'></i>
        </button>

        <button 
            data-toggle='modal'
            data-id='".$model->id."'
            onclick=\"deleteWhatsappUser('".$model->id."')\"
            class='btn btn-sm btn-dark btn-xs'>
            <i class='fa fa-trash' style='color:white;'></i>
        </button>
    </div>
";
                })
                ->filterColumn('UserName', function ($model, $keyword) {
                    $model->whereHas('user', function ($query) use ($keyword) {
                        $query->where('first_name', 'like', "%$keyword%");
                    });
                })
                ->filterColumn('WabaId', function ($model, $keyword) {
                    $model->where('waba_id', 'like', "%$keyword%");
                })
                ->filterColumn('PhoneNumber', function ($model, $keyword) {
                    $model->where('phone_number', 'like', "%$keyword%");
                })
                ->filterColumn('PhoneNumberId', function ($model, $keyword) {
                    $model->where('phone_number_id', 'like', "%$keyword%");
                })
                ->filterColumn('BusinessId', function ($model, $keyword) {
                    $model->where('business_id', 'like', "%$keyword%");
                })
                ->rawColumns(['PhoneNumberId', 'UserName', 'PhoneNumber', 'WabaId', 'BusinessId', 'access_token', 'created_at', 'action'])
                ->make(true);
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function whatsappUsersApi(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortField = $request->input('sort-field', 'created_at');
            $sortOrder = $request->input('sort-order', 'desc');
            $limit = $request->input('limit', 10);
            $allowedSorts = ['created_at', 'phone_number', 'waba_id', 'phone_number_id', 'business_id'];

            if (! in_array($sortField, $allowedSorts, true)) {
                $sortField = 'created_at';
            }

            $users = WhatsappIntegrationUser::with('user:id,first_name,last_name,email')
                ->when($searchString, function ($query) use ($searchString) {
                    $query->where(function ($q) use ($searchString) {
                        $q->where('phone_number', 'like', "%{$searchString}%")
                            ->orWhere('waba_id', 'like', "%{$searchString}%")
                            ->orWhere('phone_number_id', 'like', "%{$searchString}%")
                            ->orWhere('business_id', 'like', "%{$searchString}%")
                            ->orWhereHas('user', function ($userQuery) use ($searchString) {
                                $userQuery->where('first_name', 'like', "%{$searchString}%")
                                    ->orWhere('last_name', 'like', "%{$searchString}%")
                                    ->orWhere('email', 'like', "%{$searchString}%");
                            });
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

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
                    'created_at' => optional($model->created_at)->format('Y-m-d H:i'),
                ];
            });

            return successResponse('', $users);
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function whatsappClientTable($orderid)
    {
        $query = WhatsappIntegrationUser::select('*')->where('user_id', \Auth::user()->id)->where('order_id', $orderid);

        return \DataTables::of($query)
            ->orderColumn('UserName', '-created_at $1')
            ->orderColumn('PhoneNumber', '-created_at $1')
            ->orderColumn('WabaId', '-created_at $1')
            ->orderColumn('PhoneNumberId', '-created_at $1')
            ->orderColumn('BusinessId', '-created_at $1')
//            ->addColumn('UserName', function ($model) {
//                $user = User::select('first_name', 'last_name')->find($model->user_id);
//                return $user ? "{$user->first_name} {$user->last_name}" : '';
//            })
            ->addColumn('PhoneNumber', function ($model) {
                return $model->phone_number;
            })
            ->addColumn('WabaId', function ($model) {
                return $model->waba_id;
            })
            ->addColumn('PhoneNumberId', function ($model) {
//                return $model->phone_number_id;
                $token = e($model->phone_number_id); // escape for safety

                return '
    <div class="d-flex align-items-center">
        <input type="password" class="form-control form-control-sm" 
               value="'.$token.'" readonly style="width: 60px; margin-right: 8px;" />
        <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" 
                data-token="'.$token.'">
            <i class="fas fa-copy"></i>
        </button>
        <span class="copy-msg text-success ms-2" style="display:none;">'.__('message.copied').'</span>
    </div>
';
            })
            ->addColumn('BusinessId', function ($model) {
                return $model->business_id;
            })
            ->addColumn('created_at', function ($model) {
                return  getDateHtml($model->created_at);
            })
            ->addColumn('access_token', function ($model) {
                $token = e($model->access_token); // escape for safety

                return '
    <div class="d-flex align-items-center">
        <input type="password" class="form-control form-control-sm" 
               value="'.$token.'" readonly style="width: 60px; margin-right: 8px;" />
        <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" 
                data-token="'.$token.'">
            <i class="fas fa-copy"></i>
        </button>
        <span class="copy-msg text-success ms-2" style="display:none;">'.__('message.copied').'</span>
    </div>
';
            })
            ->addColumn('action', function ($model) {
                return "
    <div style='display:flex; gap:4px;'>
        <button 
            data-toggle='modal'
            data-id='".$model->id."'
            onclick=\"editWhatsappUser('".$model->id."')\"
            class='btn btn-sm btn-info btn-xs'>
            <i class='fa fa-edit' style='color:white;'></i>
        </button>

        <button 
            data-toggle='modal'
            data-id='".$model->id."'
            onclick=\"deleteWhatsappUser('".$model->id."')\"
            class='btn btn-sm btn-dark btn-xs'>
            <i class='fa fa-trash' style='color:white;'></i>
        </button>
    </div>
";
            })
            ->filterColumn('WabaId', function ($model, $keyword) {
                $model->where('waba_id', 'like', "%$keyword%");
            })
            ->filterColumn('PhoneNumber', function ($model, $keyword) {
                $model->where('phone_number', 'like', "%$keyword%");
            })
            ->filterColumn('PhoneNumberId', function ($model, $keyword) {
                $model->where('phone_number_id', 'like', "%$keyword%");
            })
            ->filterColumn('BusinessId', function ($model, $keyword) {
                $model->where('business_id', 'like', "%$keyword%");
            })
            ->rawColumns(['UserName', 'PhoneNumber', 'WabaId', 'PhoneNumberId', 'BusinessId', 'access_token', 'created_at', 'action'])
            ->make(true);
    }

//    public function saveAccessToken(Request $request){
//        try {
//            [$app_id, $app_secret] = array_values(WhatsappIntegration::select(['app_id', 'app_secret'])->first()->toArray());
//            //To get the Token
//            $code = $request->input('code');
//
//            $response = Http::get('https://graph.facebook.com/v21.0/oauth/access_token', [
//                'client_id' => $app_id,
//                'client_secret' => $app_secret,
//                'code' => $code,
//            ]);
//
//            $content = $response->json();
//
//
//            //Exchange the token to get permanent token
//            $access_token = $content['access_token'];
//
//            $getToken = Http::get('https://graph.facebook.com/v21.0/oauth/access_token', [
//                    'grant_type' => 'fb_exchange_token',
//                    'client_id' => $app_id,
//                    'client_secret' => $app_secret,
//                    'fb_exchange_token' => $access_token,
//            ]);
//
//            $content = $getToken->json();
//
//            WhatsappIntegrationUser::updateOrCreate(['user_id' => \Auth::user()->id], ['access_token' => $content['access_token'],'user_id'=>\Auth::user()->id]);
//            $this->saveNumber($content['access_token']);
//            return successResponse(__('message.updated-successfully'));
//        }catch (\Exception $exception){
//            return errorResponse($exception->getMessage());
//        }
//    }

//    public function saveNumber($access_token){
//        $phone_number_id=WhatsappIntegrationUser::where('user_id',\Auth::user()->id)->value('phone_number_id');
//        if($phone_number_id) {
//            $data = Http::get("https://graph.facebook.com/v21.0/{$phone_number_id}", [
//                'fields' => 'display_phone_number',
//                'access_token' => $access_token,
//            ]);
//            $content = $data->json();
//            WhatsappIntegrationUser::where('phone_number_id', $phone_number_id)->update(['phone_number' => $content['display_phone_number']]);
//        }
//    }

    public function saveWabaId(Request $request)
    {
        try {
            $wabaId = $request->input('waba_id');
            $phoneNumberId = $request->input('phone_number_id') ? $request->input('phone_number_id') : '';
            $business_id = $request->input('business_id');
            $url = \Session::get('whatsapp_url');
            $access_token = $this->getToken($request->input('code'));
            $phone_number = $this->getNumber($phoneNumberId, $access_token);
            $order_id = $request->input('order_id');
            WhatsappIntegrationUser::create(['user_id' => \Auth::user()->id, 'waba_id' => $wabaId,
                'phone_number_id' => $phoneNumberId, 'business_id' => $business_id,
                'user_callback_url' => $url, 'access_token' => $access_token, 'order_id' => $order_id, 'phone_number' => $phone_number]);
            \Session::forget('whatsapp_url');
            $response = Http::withToken($access_token)
                ->post("https://graph.facebook.com/v17.0/{$wabaId}/subscribed_apps");
            \Log::debug('ToCheck', [$response->json()]);

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getNumber($phone_number_id, $access_token)
    {
        if ($phone_number_id) {
            $url = $this->base_url.'/'.$this->api_version.'/'.$phone_number_id;
            $data = Http::get($url, [
                'fields' => 'display_phone_number',
                'access_token' => $access_token,
            ]);
            $content = $data->json();
            \Log::debug('getNumber', [$content]);

            return $content['display_phone_number'];
        }

        return '';
    }

    public function getToken($code)
    {
        try {
            [$app_id, $app_secret] = array_values(WhatsappIntegration::select(['app_id', 'app_secret'])->first()->toArray());
            //To get the Token
            $url = $this->base_url.'/'.$this->api_version.'/'.$this->endpoint['access_token'];
            $response = Http::get($url, [
                'client_id' => $app_id,
                'client_secret' => $app_secret,
                'code' => $code,
            ]);

            $content = $response->json();

            //Exchange the token to get permanent token
            $access_token = $content['access_token'];

            $getToken = Http::get($url, [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $app_id,
                'client_secret' => $app_secret,
                'fb_exchange_token' => $access_token,
            ]);

            $content = $getToken->json();

            return $content['access_token'];
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function deregister(Request $request)
    {
        try {
            $whatsappUser = WhatsappIntegrationUser::where('id', $request->input('id'))->first();
            $phoneNumberId = $whatsappUser->phone_number_id;
            $url = $this->base_url.'/'.$this->api_version.'/'.$phoneNumberId.'/'.$this->endpoint['deregister'];
            $response = Http::post($url, [
                'access_token' => $whatsappUser->access_token,
            ]);
            $whatsappUser->delete();
            $content = $response->json();

            return successResponse(__('message.deleted-successfully'));
        } catch (\Exception $exception) {
            $whatsappUser->delete();

            return errorResponse('Successfully Deleted From this application but in meta still it is connected.');
        }
    }

//    public function whatsappWebhook(Request $request)
//    {
//        \Log::debug('whatsappWebhook', [$request->all()]);
//        try {
//            // Handle GET request (Verification)
//            if ($request->isMethod('get')) {
//                $verify_token = WhatsappIntegration::value('verify_token');
//
//                $mode = $request->query('hub_mode');
//                $token = $request->query('hub_verify_token');
//                $challenge = $request->query('hub_challenge');
//
//                if ($mode === 'subscribe' && $token === $verify_token) {
//                    return response($challenge, 200);
//                }
//
//                return response('Forbidden', 403);
//            }
//            if ($request->isMethod('post')) {
//                $data = $request->all();
//                if (isset($data['entry']) && $data['entry'][0]['id'] !== '') {
//                    $wabaId = $data['entry'][0]['id'];
//                    $url = WhatsappIntegrationUser::where('waba_id', $wabaId)->value('user_callback_url');
//
//                    $response = $this->client->post($url, ['json' => $data,
//                        'headers' => [
//                            'Accept' => 'application/json',
//                        ]]);
//                }
//
//                return response('EVENT_RECEIVED', 200);
//            }
//
//            return response('Method Not Allowed', 405);
//        } catch (\Exception $exception) {
//            \Log::debug('san_exp', [$exception->getMessage()]);
//        }
//    }

    public function whatsappWebhook(Request $request)
    {
        $response = '';
        try {
            // Handle GET request (Verification)
            if ($request->isMethod('get')) {
                $verify_token = WhatsappIntegration::value('verify_token');

                $mode = $request->query('hub_mode');
                $token = $request->query('hub_verify_token');
                $challenge = $request->query('hub_challenge');

                if ($mode === 'subscribe' && $token === $verify_token) {
                    return response($challenge, 200);
                }

                return response('Forbidden', 403);
            }
            if ($request->isMethod('post')) {
                $rawBody = $request->getContent();
                $data = json_decode($rawBody, true);

                $change = $data['entry'][0]['changes'][0]['value'] ?? [];

                if (isset($change['statuses'])) {
                    return response()->json(['ignored' => 'status update'], 200);
                }

                if (! isset($change['messages'])) {
                    return response()->json(['ignored' => 'not a message'], 200);
                }

                SendWhatsappMessage::dispatch($rawBody)->onQueue('whatsapp');

                // decode only if you need to read id
//                $data = json_decode($rawBody, true);
//                if (isset($data['entry']) && $data['entry'][0]['id'] !== '') {
//                    $wabaId = $data['entry'][0]['id'];
//                    $url = WhatsappIntegrationUser::where('waba_id', $wabaId)->value('user_callback_url');
//                    $response=$this->client->post($url, [
//                        'body' => $rawBody,
//                        'headers' => [
//                            'Content-Type' => 'application/json',
//                            'Accept' => 'application/json',
//                        ],
//                    ]);
//                }
                return response('EVENT_RECEIVED', 200);
            }

            return response('Method Not Allowed', 405);
        } catch (\Exception $exception) {
            \Log::debug('san_exp', [$exception->getMessage()]);
        }
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
            WhatsappIntegration::updateOrCreate(
                ['id' => 1],
                [
                    'app_id' => $app_id,
                    'app_secret' => $app_secret,
                    'config_id' => $config_id,
                    'verify_token' => $verify_token,
                ]
            );
            StatusSetting::where('id', 1)->update(['whatsapp_status' => 1]);

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function webhookUrlEdit(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:whatsapp_integration_users,id',
            'url' => 'required|string',
        ]);

        $id = $request->input('id');
        $user = \Auth::user();

        if ($user->role !== 'admin' && (int) $id !== $user->id) {
            return errorResponse(__('message.unauthorized'), 403);
        }

        try {
            WhatsappIntegrationUser::where('id', $id)->update(['user_callback_url' => $request->input('url')]);

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function directSaveWhatsapp(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required',
            'phone_number_id' => 'required',
            'access_token' => 'required',
            'waba_id' => 'required',
            'user_id' => 'required',
            'user_callback_url' => 'required',
            'business_id' => 'required',
        ]);

        try {
            WhatsappIntegrationUser::create($validated);

            return successResponse(__('message.updated-successfully'));
        } catch (\Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }
}
