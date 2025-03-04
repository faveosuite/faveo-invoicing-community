<?php

namespace App\Http\Controllers\Tenancy;

use App\CloudPopUp;
use App\Http\Controllers\Controller;
use App\Http\Controllers\License\LicenseController;
use App\Jobs\ReportExport;
use App\Model\CloudDataCenters;
use App\Model\Common\Country;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Mailjob\QueueService;
use App\Model\Order\Order;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Subscription;
use App\ThirdPartyApp;
use App\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class TenantController extends Controller
{
    private $cloud;

    public function __construct(Client $client, FaveoCloud $cloud)
    {
        $this->client = $client;
        $this->cloud = $cloud->first();

        $this->middleware('auth', ['except' => ['verifyThirdPartyToken']]);
    }

    public function viewTenant()
    {
        if ($this->cloud && $this->cloud->cloud_central_domain) {
            $app_key=null;
            $cloud = $this->cloud;
            $cloudPopUp = CloudPopUp::find(1);
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
            if($keys !== null){
            if (! $keys->app_key) {//Valdidate if the app key to be sent is valid or not
                throw new Exception(Lang::get('message.cloud_invalid_message'));
            }else{
                $app_key = $keys->app_key;
            }
            }
            $response = $this->client->request(
                'GET',
                $this->cloud->cloud_central_domain.'/tenants',
                [
                    'query' => [
                        'key' => $app_key,
                    ],
                ]
            );

            $responseBody = (string) $response->getBody();
            $responseData = json_decode($responseBody, true);

            $de = collect($responseData['message'])->paginate(5);
        } else {
            $de = null;
            $cloudButton = null;
            $cloud = null;
            $cloudPopUp = null;
        }
        $cloudButton = StatusSetting::value('cloud_button');
        $cloudDataCenters = CloudDataCenters::all();

        // Format the results as per the specified format
        $regions = $cloudDataCenters->map(function ($center) {
            return [
                'name' => ! empty($center->cloud_city) ? $center->cloud_city.', '.$center->cloud_countries : $center->cloud_state.', '.$center->cloud_countries,
                'latitude' => $center->latitude,
                'longitude' => $center->longitude,
            ];
        });

        return view('themes.default1.tenant.index', compact('de', 'cloudButton', 'cloud', 'regions', 'cloudPopUp'));
    }





    public function enableCloud(Request $request)
    {
        try {
            $request->input('debug') == 'true' ? StatusSetting::where('id', '1')->update(['cloud_button' => '1']) : StatusSetting::where('id', '1')->update(['cloud_button' => '0']);

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch(\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getTenants(Request $request)
    {
        try {
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();

            if (! $keys->app_key) {//Valdidate if the app key to be sent is valid or not
                throw new Exception('Invalid App key provided. Please contact admin.');
            }
            $response = $this->client->request(
                'GET',
                $this->cloud->cloud_central_domain.'/tenants',
                [
                    'query' => [
                        'key' => $keys->app_key,
                    ],
                ]
            );

            $responseBody = (string) $response->getBody();
            $responseData = json_decode($responseBody);

            $collection = collect($responseData->message)->reject(function ($item) {
                return $item === null;
            });

            return \DataTables::collection($collection)
                ->addColumn('Order', function ($model) {
                    $order_id = \DB::table('installation_details')->where('installation_path', $model->domain)->latest()->value('order_id');
                    $order_number = \DB::table('orders')->where('id', $order_id)->value('number');
                    if (empty($order_id) || empty($order_number)) {
                        return '--';
                    }
                    $badge = 'badge';
                    $plan_id = Subscription::where('order_id', $order_id)->latest()->value('plan_id');
                    $price = PlanPrice::where('plan_id', $plan_id)->latest()->value('add_price');
                    $message = ($price) ? 'Paid Subscription' : 'Free Trial';
                    $badgeclass = ($price) ? 'badge-success' : 'badge-info';

                    return "<p><a href='".url('/orders/'.$order_id)."'>$order_number</a> <span class='".$badge.' '.$badgeclass."'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top'>

                         </label>".
                        $message.'</span></p>';
                })
                ->addColumn('name', function ($model) {
                    $order_id = \DB::table('installation_details')
                        ->where('installation_path', $model->domain)
                        ->latest()
                        ->value('order_id');

                    if (! $order_id) {
                        return '--';
                    }

                    $userId = Order::where('id', $order_id)->value('client');
                    if (! $userId) {
                        return '--';
                    }

                    $user = User::find($userId);
                    if (! $user) {
                        return '--';
                    }

                    return '<a href="'.url('clients/'.$user->id).'">'.e(ucfirst($user->first_name)).' '.e(ucfirst($user->last_name)).'</a>';
                })
                ->addColumn('email', function ($model) {
                    $order_id = \DB::table('installation_details')
                        ->where('installation_path', $model->domain)
                        ->latest()
                        ->value('order_id');

                    if (! $order_id) {
                        return '--';
                    }

                    $userId = Order::where('id', $order_id)->value('client');
                    if (! $userId) {
                        return '--';
                    }

                    $user = User::find($userId);
                    if (! $user) {
                        return '--';
                    }

                    return $user->email ?? '';
                })
                   ->addColumn('mobile', function ($model) {
                       $order_id = \DB::table('installation_details')
                           ->where('installation_path', $model->domain)
                           ->latest()
                           ->value('order_id');

                       if (! $order_id) {
                           return '--';
                       }

                       $userId = Order::where('id', $order_id)->value('client');
                       if (! $userId) {
                           return '--';
                       }

                       $user = User::find($userId);
                       if (! $user) {
                           return '--';
                       }

                       return isset($user->mobile_code) && isset($user->mobile) ? '+'.$user->mobile_code.' '.$user->mobile : '--';
                   })

                   ->addColumn('country', function ($model) {
                       $order_id = \DB::table('installation_details')
                           ->where('installation_path', $model->domain)
                           ->latest()
                           ->value('order_id');

                       if (! $order_id) {
                           return '--';
                       }

                       $userId = Order::where('id', $order_id)->value('client');
                       if (! $userId) {
                           return '--';
                       }

                       $user = User::find($userId);
                       $country = Country::where('country_code_char2', $user->country)->value('nicename');
                       if (! $user) {
                           return '--';
                       }

                       return $country ?? '';
                   })

                   ->addColumn('Expiry day', function ($model) {
                       $order_id = \DB::table('installation_details')->where('installation_path', $model->domain)->latest()->value('order_id');
                       $subscription_date = Subscription::where('order_id', $order_id)->value('ends_at');
                       if (empty($subscription_date)) {
                           return '--';
                       }

                       return getDateHtml($subscription_date);
                   })

                ->addColumn('Deletion day', function ($model) {
                    $order_id = \DB::table('installation_details')->where('installation_path', $model->domain)->latest()->value('order_id');
                    $subscription_date = Subscription::where('order_id', $order_id)->value('ends_at');
                    if (empty($subscription_date)) {
                        return '--';
                    }
                    $days = \DB::table('expiry_mail_days')->where('cloud_days', '!=', null)->value('cloud_days');
                    $originalDate = Carbon::parse($subscription_date)->addDays($days);
                    $formattedDate = Carbon::parse($originalDate)->format('d M Y');

                    return $formattedDate;
                })

               ->addColumn('plan', function ($model) {
                   $order_id = \DB::table('installation_details')->where('installation_path', $model->domain)->latest()->value('order_id');
                   if (empty($order_id)) {
                       return '--';
                   }

                   $plan_id = Subscription::where('order_id', $order_id)->latest()->value('plan_id');
                   $price = PlanPrice::where('plan_id', $plan_id)->latest()->value('add_price');
                   $message = ($price) ? 'Paid Subscription' : 'Free Trial';

                   return $message;
               })

                ->addColumn('tenants', function ($model) {
                    return $model->id ?? '';
                })
                ->addColumn('domain', function ($model) {
                    return '<a href="http://'.$model->domain.'" target="_blank">'.$model->domain.'</a>';
                })
                ->addColumn('db_name', function ($model) {
                    return $model->database_name ?? '';
                })
                ->addColumn('db_username', function ($model) {
                    return $model->database_user_name ?? '';
                })
                ->addColumn('action', function ($model) {
                    $order_id = \DB::table('installation_details')->where('installation_path', $model->domain)->value('order_id');
                    $order_number = \DB::table('orders')->where('id', $order_id)->value('number');

                    if (empty($order_id) || empty($order_number)) {
                        return "<p><button data-toggle='modal'
                data-id=".$model->id." data-name= '' onclick=deleteTenant('".$model->id."') id='delten".$model->id."'
                class='btn btn-sm btn-dark btn-xs delTenant'".tooltip('Delete')."<i class='fa fa-trash'
                style='color:white;'> </i></button>&nbsp;</p>";
                    }

                    return "<p><button data-toggle='modal'
                data-id='".$model->id."' data-name='' onclick=\"deleteTenant('".$model->id."','".$order_number."')\" id='delten".$model->id."'
                class='btn btn-sm btn-dark btn-xs delTenant' ".tooltip('Delete')."<i class='fa fa-trash'
                style='color:white;'> </i></button>&nbsp;</p>";
                })
                ->rawColumns(['Order', 'Deletion day', 'tenants', 'domain', 'db_name', 'db_username', 'action', 'name', 'email', 'mobile', 'country', 'Expiry day', 'plan'])
                ->make(true);
        } catch (ConnectException|Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    private function postCurl($post_url, $post_info)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $post_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_info);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Logic for creating new tenant is handled here.
     */
    public function createTenant(Request $request)
    {
        $order = Order::wherenumber($request->orderNo)->get();
        $product = CloudProducts::where('cloud_product', $order[0]->product()->value('id'))->value('cloud_product_key');

        $this->validate($request,
            [
                'orderNo' => 'required',
                'domain' => 'required||regex:/^[a-zA-Z0-9]+$/u',
            ],
            [
                'domain.regex' => 'Special characters are not allowed in domain name',
            ]);

        $settings = Setting::find(1);
        $userInformation = $request->has('userInfo') ? User::find($request->input('userInfo')) : \Auth::user();

        $userEmail = $userInformation->email;
        $userFirstName = $userInformation->first_name;
        $userLastName = $userInformation->last_name;
        $userId = $userInformation->id;

        $mail = new \App\Http\Controllers\Common\PhpMailController();

        try {
            $company = (string) $request->input('domain');

            // Convert spaces to underscores
            $company = str_replace(' ', '', $company);

            // Convert uppercase letters to lowercase
            $faveoCloud = strtolower($company).'.'.cloudSubDomain();

            $dns_record = dns_get_record($faveoCloud, DNS_CNAME);
            if (! strpos($faveoCloud, cloudSubDomain())) {
                if (empty($dns_record) || ! in_array(cloudSubDomain(), array_column($dns_record, 'target'))) {
                    return ['status' => 'false', 'message' => trans('message.cname')];
                }
            }
            $licCode = Order::where('number', $request->input('orderNo'))->first()->serial_key;
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
            if (! $keys->app_key) {//Valdidate if the app key to be sent is valid or not
                throw new Exception('Invalid App key provided. Please contact admin.');
            }
            $token = str_random(32);
            \DB::table('third_party_tokens')->insert(['user_id' => $userId, 'token' => $token]);
            $client = new Client([]);
            $data = ['domain' => $faveoCloud, 'app_key' => $keys->app_key, 'token' => $token, 'lic_code' => $licCode, 'username' => $userEmail, 'userId' => $userId, 'timestamp' => time(), 'product' => $product, 'product_id' => $order[0]->product()->value('id')];
            $encodedData = http_build_query($data);
            $hashedSignature = hash_hmac('sha256', $encodedData, $keys->app_secret);
            $response = $client->request(
                'POST',
                $this->cloud->cloud_central_domain.'/tenants', ['form_params' => $data, 'headers' => ['signature' => $hashedSignature]]
            );

            $response = explode('{', (string) $response->getBody());

            $response = '{'.$response[1];

            $result = json_decode($response);
            if ($result->status == 'fails') {
                if ($result->message == 'Domain already taken. Please select a different domain') {
                    $newRandomDomain = substr($product.str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 28);

                    return $this->createTenantWithRandomDomain($newRandomDomain, $request);
                }

                $this->prepareMessages($faveoCloud, $userEmail);

                $this->googleChat($result->message);

                return ['status' => 'false', 'message' => trans('message.something_bad')];
            } elseif ($result->status == 'validationFailure') {
                $this->prepareMessages($faveoCloud, $userEmail);

                $this->googleChat($result->message);

                return ['status' => 'validationFailure', 'message' => $result->message];
            } else {
                $client->request('GET', env('CLOUD_JOB_URL_NORMAL'), [
                    'auth' => [env('CLOUD_USER'), env('CLOUD_AUTH')],
                    'query' => [
                        'token' => env('CLOUD_OAUTH_TOKEN'),
                        'domain' => $faveoCloud,
                    ],
                ]);

                //template
                $template = new \App\Model\Common\Template();
                $temp_type_id = \DB::table('template_types')->where('name', 'cloud_created')->value('id');
                $template = $template->where('type', $temp_type_id)->first();
                $contact = getContactData();

                $type = '';
                if ($template) {
                    $type_id = $template->type;
                    $temp_type = new \App\Model\Common\TemplateType();
                    $type = $temp_type->where('id', $type_id)->first()->name;
                }
                $subject = 'Your '.$order[0]->product()->value('name').' is now ready for use. Get started!';
                $result->message = str_replace('website', strtolower($product), $result->message);
                $result->message = str_replace('You will receive password on your registered email', '', $result->message);
                $userData = $result->message.'<br><br> Email:'.' '.$userEmail.'<br>'.'Password:'.' '.$result->password;

                $replace = [
                    'message' => $userData,
                    'product' => $order[0]->product()->value('name'),
                    'name' => $userFirstName.' '.$userLastName,
                    'contact' => $contact['contact'],
                    'logo' => $contact['logo'],
                    'title' => $settings->title,
                    'company_email' => $settings->company_email,
                    'reply_email' => $settings->company_email,

                ];

                $this->prepareMessages($faveoCloud, $userEmail, true);
                $mail->SendEmail($settings->email, $userEmail, $template->data, $subject, $replace, $type);

                return ['status' => $result->status, 'message' => $result->message.trans('message.cloud_created_successfully')];
            }
        } catch (Exception $e) {
            $message = $e->getMessage().' Domain: '.$faveoCloud.' Email: '.$userEmail;
            $this->googleChat($message);

            return ['status' => 'false', 'message' => trans('message.something_bad')];
        }
    }

    public function verifyThirdPartyToken(Request $request)
    {
        try {
            $token = $request->input('token');
            $userId = $request->input('userId');
            $faveoToken = \DB::table('third_party_tokens')->where('user_id', $userId)->value('token');
            if ($faveoToken && $token == $faveoToken) {
                \DB::table('third_party_tokens')->where('user_id', $userId)->delete();
                //delete third party token here
                $response = ['status' => 'success', 'message' => 'Valid token'];
            } else {
                $response = ['status' => 'fails', 'message' => 'Invalid token'];
            }

            return $response;
        } catch (Exception $e) {
            $error = ['status' => 'fails', 'message' => $e->getMessage()];

            return $error;
        }
    }

    public function destroyTenant(Request $request)
    {
        try {
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
            $token = str_random(32);
            $data = ['id' => $request->input('id'), 'app_key' => $keys->app_key, 'deleteTenant' => true, 'token' => $token, 'timestamp' => time()];
            $encodedData = http_build_query($data);
            $hashedSignature = hash_hmac('sha256', $encodedData, $keys->app_secret);
            $client = new Client([]);
            $response = $client->request(
                'DELETE',
                $this->cloud->cloud_central_domain.'/tenants', ['form_params' => $data, 'headers' => ['signature' => $hashedSignature]]
            );
            $responseBody = (string) $response->getBody();
            $response = json_decode($responseBody);
            if ($response->status == 'success') {
                $this->deleteCronForTenant($request->input('id'));
                \DB::table('free_trial_allowed')->where('domain', $request->input('id'))->delete();
                (empty($request->orderId)) ?: Order::where('id', $request->get('orderId'))->delete();
                (new LicenseController())->reissueDomain($request->input('id'));

                $user = optional(\Auth::user())->email ?? 'Auto deletion';

                $this->googleChat('Hello, it has come to my notice that '.$user.' has deleted this cloud instance '.$request->input('id'));

                return successResponse($response->message);
            } else {
                return errorResponse($response->message);
            }
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    private function deleteCronForTenant($tenantId)
    {
        $client = new Client();
        if (strpos($tenantId, cloudSubDomain())) {
            $client->request('GET', env('CLOUD__DELETE_JOB_URL_NORMAL'), [
                'auth' => [env('CLOUD_USER'), env('CLOUD_AUTH')],
                'query' => [
                    'token' => env('CLOUD_OAUTH_TOKEN'),
                    'domain' => $tenantId,
                ],
            ]);
        } else {
            $client->request('GET', env('CLOUD__DELETE_JOB_URL_CUSTOM'), [
                'auth' => [env('CLOUD_USER'), env('CLOUD_AUTH')],
                'query' => [
                    'token' => env('CLOUD_OAUTH_TOKEN'),
                    'domain' => $tenantId,
                ],
            ]);
        }
    }

    public function saveCloudDetails(Request $request)
    {
        $this->validate($request, [
            'cloud_central_domain' => 'required',
            'cloud_cname' => 'required',
        ]);

        try {
            $cloud = new FaveoCloud;
            $cloud->updateOrCreate(['id' => 1], ['cloud_central_domain' => $request->input('cloud_central_domain'), 'cloud_cname' => $request->input('cloud_cname')]);

            // $cloud->first()->fill($request->all())->save();
            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function DeleteCloudInstanceForClient($orderNumber, $isDelete)
    {
        if ($isDelete) {
            $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
            $token = str_random(32);
            $order_id = Order::where('number', $orderNumber)->where('client', \Auth::user()->id)->value('id');
            $installation_path = \DB::table('installation_details')->where('order_id', $order_id)->where('installation_path', '!=', cloudCentralDomain())->value('installation_path');
            $response = $this->client->request(
                'GET',
                $this->cloud->cloud_central_domain.'/tenants', [
                    'query' => [
                        'key' => $keys->app_key,
                    ],
                ]
            );
            $responseBody = (string) $response->getBody();
            $response = json_decode($responseBody);
            $domainArray = $response->message;
            for ($i = 0; $i < count($domainArray); $i++) {
                if (! is_null($domainArray[$i])) {
                    if ($domainArray[$i]->domain == $installation_path) {
                        $data = ['id' => $domainArray[$i]->id, 'app_key' => $keys->app_key, 'deleteTenant' => true, 'token' => $token, 'timestamp' => time()];
                        $encodedData = http_build_query($data);
                        $hashedSignature = hash_hmac('sha256', $encodedData, $keys->app_secret);
                        $client = new Client([]);
                        $response = $client->request(
                            'DELETE',
                            $this->cloud->cloud_central_domain.'/tenants', ['form_params' => $data, 'headers' => ['signature' => $hashedSignature]]
                        );
                        $responseBody = (string) $response->getBody();
                        $response = json_decode($responseBody);
                        if ($response->status == 'success') {
                            $this->deleteCronForTenant($domainArray[$i]->id);
                            $this->reissueCloudLicense($order_id);
                            Order::where('number', $orderNumber)->where('client', \Auth::user()->id)->delete();
                            \DB::table('free_trial_allowed')->where('domain', $installation_path)->delete();

                            $user = optional(\Auth::user())->email ?? 'Auto deletion';

                            $this->googleChat('Hello, it has come to my notice that '.$user.' has deleted this cloud instance '.$installation_path);

                            return redirect()->back()->with('success', $response->message);
                        } else {
                            return redirect()->back()->with('fails', $response->message);
                        }
                    }
                }
            }

            return redirect()->back()->with('fails', "Something went wrong, we couldn't delete your cloud instance.(mostly your cloud instance was already deleted)");
        }
    }

    protected function reissueCloudLicense($order_id)
    {
        $order = Order::findorFail($order_id);
        if (\Auth::user()->role != 'admin' && $order->client != \Auth::user()->id) {
            return errorResponse('Cannot remove license installations. Invalid modification of data');
        }
        $order->domain = '';
        $licenseCode = $order->serial_key;
        $order->save();
        $licenseStatus = \DB::table('status_settings')->pluck('license_status')->first();
        if ($licenseStatus == 1) {
            $licenseExpiry = $order->subscription->ends_at;
            $updatesExpiry = $order->subscription->update_ends_at;
            $supportExpiry = $order->subscription->support_ends_at;
            $cont = new \App\Http\Controllers\License\LicenseController();
            $updateLicensedDomain = $cont->updateLicensedDomain($licenseCode, $order->domain, $order->product, $licenseExpiry, $updatesExpiry, $supportExpiry, $order->number);
            //Now make Installation status as inactive
            $updateInstallStatus = $cont->updateInstalledDomain($licenseCode, $order->product);
        }

        return ['message' => 'success', 'update' => 'License installations removed'];
    }

    private function prepareMessages($domain, $user, $success = false)
    {
        if ($success) {
            $this->googleChat('Hello, It has come to my notice that this domain has been created successfully Domain name:'.$domain.' and this is their email: '.$user."\u{2705}\u{2705}\u{2705}");
        } else {
            $this->googleChat('Hello, It has come to my notice that this domain has not been created successfully Domain name:'.$domain.' and this is their email: '.$user.'&#10060;'."\u{2716}\u{2716}\u{2716}");
        }
    }

    private function googleChat($text)
    {
        $url = env('GOOGLE_CHAT');
        $message = [
            'text' => $text,
        ];
        $message_headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
        ];
        $client = new Client();
        $client->post($url, [
            'headers' => $message_headers,
            'body' => json_encode($message),
        ]);
    }

    private function createTenantWithRandomDomain($randomDomain, Request $request)
    {
        // Modify the request with the new random domain
        $request->merge(['domain' => $randomDomain]);

        // Call the createTenant function with the modified request
        return $this->createTenant($request);
    }

    public function cloudPopUp(Request $request)
    {
        $this->validate($request, [
            'cloud_top_message' => 'required',
            'cloud_label_field' => 'required',
            'cloud_label_radio' => 'required',
        ]);

        try {
            $cloud = new CloudPopUp;
            $cloud->updateOrCreate(['id' => 1], ['cloud_top_message' => $request->input('cloud_top_message'),
                'cloud_label_field' => $request->input('cloud_label_field'),
                'cloud_label_radio' => $request->input('cloud_label_radio')]);

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function cloudProductStore(Request $request)
    {
        $request->validate(
            [
                'cloud_product' => 'required',
                'cloud_free_plan' => 'required',
                'cloud_product_key' => 'required',
            ]
        );
        try {
            CloudProducts::create($request->all());

            return redirect()->back()->with('success', trans('message.saved_products'));
        } catch(\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function exportTenats(Request $request)
    {
        try {
            ini_set('memory_limit', '-1');
            $selectedColumns = $request->input('selected_columns', []);
            $searchParams = $request->input('search_params', []);
            $email = \Auth::user()->email;
            $driver = QueueService::where('status', '1')->first();
            if ($driver->name != 'Sync') {
                app('queue')->setDefaultDriver($driver->short_name);
                ReportExport::dispatch('tenats', $selectedColumns, $searchParams, $email)->onQueue('reports');

                return response()->json(['message' => 'System is generating your report. You will be notified once completed'], 200);
            } else {
                return response()->json(['message' => 'Cannot use sync queue driver for export'], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Export failed: '.$e->getMessage());

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
