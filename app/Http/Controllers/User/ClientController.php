<?php

namespace App\Http\Controllers\User;

use App\Comment;
use App\ExportDetail;
use App\Http\Controllers\License\LicenseController;
use App\Http\Requests\User\ClientRequest;
use App\Jobs\ReportExport;
use App\Model\Common\Country;
use App\Model\Common\StatusSetting;
use App\Model\Mailjob\QueueService;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Payment\Currency;
use App\Model\User\AccountActivate;
use App\ReportColumn;
use App\Traits\PaymentsAndInvoices;
use App\User;
use App\UserLinkReport;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class ClientController extends AdvanceSearchController
{
    use PaymentsAndInvoices;

    public $user;

    public $activate;

    public $product;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $user = new User();
        $this->user = $user;
        $activate = new AccountActivate();
        $this->activate = $activate;
        $product = new \App\Model\Product\Product();
        $this->product = $product;
        $license = new LicenseController();
        $this->licensing = $license;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'reg_from' => 'nullable',
            'reg_till' => 'nullable|after:reg_from',

        ]);
        if ($validator->fails()) {
            $request->reg_from = '';
            $request->reg_till = '';

            return redirect('clients')->with('fails', 'Registered from should be before registered till date');
        }

        return view('themes.default1.user.client.index', compact('request'));
    }

    /**
     * Get Clients for yajra datatable.
     *
     * @param  Request  $request
     * @return
     *
     * @throws \Exception
     */
    public function getClients(Request $request)
    {
        $baseQuery = $this->getBaseQueryForUserSearch($request);

        // dd($baseQuery->get()->last());
        return DataTables::of($baseQuery)
                        ->orderColumn('name', '-id $1')
                        ->orderColumn('email', '-id $1')
                        ->orderColumn('mobile', '-id $1')
                        ->orderColumn('country', '-id $1')
                        ->orderColumn('created_at', '-id $1')
                        ->orderColumn('active', '-id $1')

                        ->addColumn('checkbox', function ($model) {
                            $isAccountManager = User::where('account_manager', $model->id)->get();
                            $isSalesManager = User::where('manager', $model->id)->get();
                            if (count($isSalesManager)) {
                                return "<input type='checkbox' disabled> &nbsp;
                        <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);' ".'<label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="This user cannot be deleted as he/she is existing sales manager for certain clients. Please replace Sales Manager from settings and then try deleting.">
                        </label>'.'</i>';
                            } elseif (count($isAccountManager)) {
                                // dd("<input type='checkbox' ".tooltip('dsf')."'disabled'");
                                return "<input type='checkbox' disabled> &nbsp;
                        <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);' ".'<label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="This user cannot be deleted as he/she is existing account manager for certain clients. Please replace Account Manager from settings and then try deleting.">
                        </label>'.'</i>';
                            } else {
                                return "<input type='checkbox' class='user_checkbox' value=".$model->id.' name=select[] id=check>';
                            }
                        })
                        ->addColumn('name', function ($model) {
                            return '<a href='.url('clients/'.$model->id).'>'.ucfirst($model->name).'</a>';
                        })
                         ->addColumn('email', function ($model) {
                             return $model->email;
                         })
                        ->addColumn('mobile', function ($model) {
                            return $model->mobile;
                        })
                        ->addColumn('country', function ($model) {
                            return ucfirst(strtolower($model->country));
                        })
                        ->addColumn('company', function ($model) {
                            return $model->company;
                        })
                        ->addColumn('created_at', function ($model) {
                            return getDateHtml($model->created_at);
                        })
                        ->addColumn('active', function ($model) {
                            return $this->getActiveLabel($model->mobile_verified, $model->active, $model->is_2fa_enabled);
                        })
                        ->addColumn('action', function ($model) {
                            return '<a href='.url('clients/'.$model->id.'/edit')
                            ." class='btn btn-sm btn-secondary btn-xs'".tooltip('Edit')."
                            <i class='fa fa-edit' style='color:white;'> </i></a>"
                                    .'  <a href='.url('clients/'.$model->id)
                                    ." class='btn btn-sm btn-secondary btn-xs'".tooltip('View')."
                                    <i class='fa fa-eye' style='color:white;'> </i></a>";
                        })

                        ->filterColumn('name', function ($model, $keyword) {
                            // removing all white spaces so that it can be searched irrespective of number of spaces
                            $model->whereRaw("CONCAT(first_name, ' ',last_name) like ?", ["%$keyword%"]);
                        })
                        ->filterColumn('email', function ($model, $keyword) {
                            $model->whereRaw('email like ?', ["%$keyword%"]);
                        })
                        ->filterColumn('mobile', function ($model, $keyword) {
                            // removing all white spaces so that it can be searched in a single query
                            $searchQuery = str_replace(' ', '', $keyword);
                            $model->whereRaw("CONCAT('+', mobile_code, mobile) like ?", ["%$searchQuery%"]);
                        })
                        ->filterColumn('country', function ($model, $keyword) {
                            // removing all white spaces so that it can be searched in a single query
                            $searchQuery = str_replace(' ', '', $keyword);
                            $model->whereRaw('country_name like ?', ["%$searchQuery%"]);
                        })
                        ->orderColumn('name', 'name $1')
                        ->orderColumn('email', 'email $1')
                        ->orderColumn('mobile', 'mobile $1')
                        ->orderColumn('country', 'country $1')
                        ->orderColumn('created_at', 'created_at $1')

                        ->rawColumns(['checkbox', 'name', 'email',  'created_at', 'active', 'action'])
                        ->make(true);
    }

    public function getActiveLabel($mobileActive, $emailActive, $twoFaActive)
    {
        $emailLabel = "<i class='fas fa-envelope'  style='color:red'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='Unverified email'> </label></i>";
        $mobileLabel = "<i class='fas fa-phone'  style='color:red'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='Unverified mobile'>  </label></i>";
        $twoFalabel = "<i class='fas fa-qrcode'  style='color:red'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='2FA not enabled'> </label></i>";
        if ($mobileActive) {
            $mobileLabel = "<i class='fas fa-phone'  style='color:green'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='Mobile verified'></label></i>";
        }
        if ($emailActive) {
            $emailLabel = "<i class='fas fa-envelope'  style='color:green'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='Email verified'> </label></i>";
        }
        if ($twoFaActive) {
            $twoFalabel = "<i class='fas fa-qrcode'  style='color:green'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='2FA Enabled'> </label></i>";
        }

        return $emailLabel.'&nbsp;&nbsp;'.$mobileLabel.'&nbsp;&nbsp;'.$twoFalabel;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Response
     */
    public function create()
    {
        $timezones = new \App\Model\Common\Timezone();
        $timezones = $timezones->pluck('name', 'id')->toArray();
        $bussinesses = \App\Model\Common\Bussiness::pluck('name', 'short')->toArray();
        $managers = User::where('role', 'admin')->where('position', 'manager')
        ->pluck('first_name', 'id')->toArray();
        $accountManager = User::where('role', 'admin')->where('position', 'account_manager')
        ->pluck('first_name', 'id')->toArray();
        $timezonesList = \App\Model\Common\Timezone::get();
        foreach ($timezonesList as $timezone) {
            $location = $timezone->location;
            if ($location) {
                $start = strpos($location, '(');
                $end = strpos($location, ')', $start + 1);
                $length = $end - $start;
                $result = substr($location, $start + 1, $length - 1);
                $display[] = ['id' => $timezone->id, 'name' => '('.$result.')'.' '.$timezone->name];
            }
        }
        $timezones = array_column($display, 'name', 'id');

        return view('themes.default1.user.client.create', compact('timezones', 'bussinesses', 'managers', 'accountManager'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Response
     */
    public function store(ClientRequest $request)
    {
        try {
            $user = $this->user;
            $str = 'demopass';
            $password = \Hash::make($str);
            $user->password = $password;
            if ($request->input('mobile_code') == '') {
                $country = new Country();
                $mobile_code = $country->where('country_code_char2', $request->input('country'))->pluck('phonecode')->first();
            } else {
                $mobile_code = str_replace('+', '', $request->input('mobile_code'));
            }
            $location = getLocation();
            $user = [
                'user_name' => $request->input('user_name'),
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'email' => $request->input('email'),
                'password' => $password,
                'company' => $request->input('company'),
                'bussiness' => $request->input('bussiness'),
                'active' => $request->input('active'),
                'mobile_verified' => $request->input('mobile_verified'),
                'role' => $request->input('role'),
                'position' => $request->input('position'),
                'company_type' => $request->input('company_type'),
                'company_size' => $request->input('company_size'),
                'address' => $request->input('address'),
                'town' => $request->input('town'),
                'country' => strtoupper($request->input('country')),
                'state' => $request->input('state'),
                'zip' => $request->input('zip'),
                'timezone_id' => $request->input('timezone_id'),
                'mobile_code' => $mobile_code,
                'mobile' => $request->input('mobile'),
                'skype' => $request->input('skype'),
                'manager' => $request->input('manager'),
                'account_manager' => $request->input('account_manager'),
                'ip' => $location['ip'],
            ];
            $userInput = User::create($user);

            if (emailSendingStatus()) {
                $this->sendWelcomeMail($userInput);
            }
            $mailchimpStatus = StatusSetting::first()->value('mailchimp_status');
            if ($mailchimpStatus == 1) {
                $mailchimp = new \App\Http\Controllers\Common\MailChimpController();
                $r = $mailchimp->addSubscriber($user);
            }

            return redirect()->back()->with('success', \Lang::get('message.saved-successfully'));
        } catch (\Swift_TransportException $e) {
            return redirect()->back()->with('warning', 'User has been created successfully
             But email configuration has some problem!!'.$e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Response
     */
    public function show($id)
    {
        try {
            if (User::onlyTrashed()->find($id)) {
                throw new \Exception(\Lang::get('messages.user_suspend'));
            }
            $invoice = new Invoice();
            $order = new Order();
            $invoices = $invoice->where('user_id', $id)->orderBy('created_at', 'desc')->get();
            $invoiceSum = $this->getTotalInvoice($invoices);
            $amountReceived = $this->getAmountPaid($id);
            $pendingAmount = $invoiceSum - $amountReceived;
            // $pendingAmount = $invoiceSum - $amountReceived;
            // if ($pendingAmount < 0) {
            //     $pendingAmount = 0;
            // }
            $extraAmt = $this->getExtraAmt($id);
            $client = $this->user->where('id', $id)->first();

            if (User::onlyTrashed()->find($id)) {
                $client = User::onlyTrashed()->find($id);
            }

            $is2faEnabled = $client->is_2fa_enabled;
            $currency = getCurrencyForClient($client->country);
            $orders = $order->where('client', $id)->get();
            $comments = Comment::where('user_id', $client->id)->get();
            $mobile = $client->verificationAttempts()->value('mobile_attempt');
            $email = $client->verificationAttempts()->value('email_attempt');

            return view(
                'themes.default1.user.client.show',
                compact('id', 'client', 'invoices', 'orders', 'invoiceSum', 'amountReceived', 'pendingAmount', 'currency', 'extraAmt', 'comments',
                    'is2faEnabled', 'email', 'mobile')
            );
        } catch (\Exception $ex) {
            app('log')->info($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Response
     */
    public function edit($id)
    {
        try {
            $user = $this->user->where('id', $id)->first();
            $timezonesList = \App\Model\Common\Timezone::get();
            foreach ($timezonesList as $timezone) {
                $location = $timezone->location;
                if ($location) {
                    $start = strpos($location, '(');
                    $end = strpos($location, ')', $start + 1);
                    $length = $end - $start;
                    $result = substr($location, $start + 1, $length - 1);
                    $display[] = ['id' => $timezone->id, 'name' => '('.$result.')'.' '.$timezone->name];
                }
            }
            //for display
            $timezones = array_column($display, 'name', 'id');
            $state = getStateByCode($user->state);
            $managers = User::select(DB::raw('CONCAT(first_name, " ", last_name) As first_name'), 'id')
            ->where('role', 'admin')->where('position', 'manager')
            ->get()->pluck('first_name', 'id');
            $acc_managers = User::select(DB::raw('CONCAT(first_name, " ", last_name) As first_name'), 'id')
            ->where('role', 'admin')->where('position', 'account_manager')->get()
            ->pluck('first_name', 'id');
            $selectedCurrency = Currency::where('code', $user->currency)
            ->pluck('name', 'code')->toArray();
            $selectedCompany = \DB::table('company_types')->where('name', $user->company_type)
            ->pluck('name', 'short')->toArray();
            $selectedIndustry = \App\Model\Common\Bussiness::where('name', $user->bussiness)
            ->pluck('name', 'short')->toArray();
            $selectedCompanySize = \DB::table('company_sizes')->where('short', $user->company_size)
            ->pluck('name', 'short')->toArray();
            $states = findStateByRegionId($user->country);

            $bussinesses = \App\Model\Common\Bussiness::pluck('name', 'short')->toArray();

            return view(
                'themes.default1.user.client.edit',
                compact(
                    'bussinesses',
                    'user',
                    'timezones',
                    'state',
                    'states',
                    'managers',
                    'selectedCurrency',
                    'selectedCompany',
                    'selectedIndustry',
                    'selectedCompanySize',
                    'acc_managers'
                )
            );
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function update($id, ClientRequest $request)
    {
        try {
            $user = $this->user->where('id', $id)->first();
            $user->fill($request->input())->save();

            // \Session::put('test', 1000);
            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (\Exception $ex) {
            app('log')->error($ex->getMessage());

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('select');
            if (! empty($ids)) {
                foreach ($ids as $id) {
                    $user = $this->user->where('id', $id)->first();
                    //Check if this admin  is account manager and is assigned as account manager to other clients
                    $isAccountManager = User::where('account_manager', $id)->get();
                    $isSalesManager = User::where('manager', $id)->get();
                    if (count($isSalesManager) > 0) {
                        throw new \Exception('Admin'.' '.$user->first_name.' '.$user->last_name.' '.'cannot be deleted as he/she is existing sales manager for certain clients. Please replace Sales Manager from settings and then try deleting.');
                    }
                    if (count($isAccountManager) > 0) {
                        throw new \Exception('Admin'.' '.$user->first_name.' '.$user->last_name.' '.'cannot be deleted as he/she is existing account manager for certain clients. Please replace Account Manager from settings and then try deleting.');
                    }
                    if ($user) {
                        $user->delete();
                    } else {
                        echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */
                    \Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.no-record').'
                </div>';
                        //echo \Lang::get('message.no-record') . '  [id=>' . $id . ']';
                    }
                }
                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert')
                    .'!</b> './* @scrutinizer ignore-type */
                    \Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.deleted-successfully').'
                </div>';
            } else {
                echo "<div class='alert alert-success alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '
                    ./* @scrutinizer ignore-type */\Lang::get('message.success').'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        './* @scrutinizer ignore-type */\Lang::get('message.select-a-row').'
                </div>';
            }
        } catch (\Exception $e) {
            echo "<div class='alert alert-danger alert-dismissable'>
                    <i class='fa fa-ban'></i>
                    <b>"./* @scrutinizer ignore-type */\Lang::get('message.alert').'!</b> '.
                    /* @scrutinizer ignore-type */'
                    <button type=button class=close data-dismiss=alert aria-hidden=true>&times;</button>
                        '.$e->getMessage().'
                </div>';
        }
    }

    public function sendWelcomeMail($user)
    {
        // Retrieve necessary data
        $contact = getContactData();
        $settings = \App\Model\Common\Setting::find(1);
        $template_type = \App\Model\Common\TemplateType::where('name', 'registration_mail')->first();
        $template = \App\Model\Common\Template::find($template_type->id);

        // Check if settings or template is missing
        if (!$settings || !$template) {
            return;
        }

        // Prepare dynamic data for email template
        $replace = [
            'name' => $user->first_name.' '.$user->last_name,
            'username' => $user->email,
            'password' => 'demopass',
            'website_url' => url('/'),
            'contact' => $contact['contact'] ?? '',
            'logo' => $contact['logo'] ?? '',
            'company_email' => $settings->company_email,
            'reply_email' => $settings->company_email,
        ];

        // Get template type name

        $type = $template->type ? \App\Model\Common\TemplateType::find($template->type)->name : '';

        // Send the email
        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $mail->SendEmail($settings->email, $user->email, $template->data, $template->name, $replace, $type);
    }


    /**
     * Gets baseQuery for user search by appending all the allowed filters.
     *
     * @param  $request
     * @return mixed
     */
    private function getBaseQueryForUserSearch(Request $request)
    {
        $baseQuery = User::leftJoin('countries', 'users.country', '=', 'countries.country_code_char2')
        ->select('id', 'first_name', 'last_name', 'email',
            \DB::raw("CONCAT('+', mobile_code, ' ', mobile) as mobile"),
            \DB::raw("CONCAT(first_name, ' ', last_name) as name"),
            'country_name as country', 'created_at', 'active', 'mobile_verified', 'is_2fa_enabled', 'role', 'position'
        );
        // Apply other conditions based on the request
        $baseQuery = $baseQuery->when($request->company, function ($query) use ($request) {
            $query->where('company', 'LIKE', '%'.$request->company.'%');
        })->when($request->country, function ($query) use ($request) {
            $query->where('country', $request->country);
        })->when($request->industry, function ($query) use ($request) {
            $query->where('bussiness', $request->industry);
        })->when($request->role, function ($query) use ($request) {
            $query->where('role', $request->role);
        })->when($request->position, function ($query) use ($request) {
            $query->where('position', $request->position);
        })->when($request->actmanager, function ($query) use ($request) {
            $query->where('account_manager', $request->actmanager);
        })->when($request->salesmanager, function ($query) use ($request) {
            $query->where('manager', $request->salesmanager);
        })->when($request->filled('mobile_verified'), function ($query) use ($request) {
            $query->where('mobile_verified', $request->mobile_verified);
        })->when($request->filled('active'), function ($query) use ($request) {
            $query->where('active', $request->active);
        })->when($request->filled('is_2fa_enabled'), function ($query) use ($request) {
            $query->where('is_2fa_enabled', $request->is_2fa_enabled);
        });

        $baseQuery = $this->getregFromTill($baseQuery, $request->reg_from, $request->reg_till);

        return $baseQuery;
    }

    public function exportUsers(Request $request)
    {
        try {
            ini_set('memory_limit', '-1');
            $selectedColumns = $request->input('selected_columns', []);
            $searchParams = $request->input('search_params', []);
            $email = \Auth::user()->email;
            $driver = QueueService::where('status', '1')->first();

            if ($driver->name != 'Sync') {
                app('queue')->setDefaultDriver($driver->short_name);
                ReportExport::dispatch('users', $selectedColumns, $searchParams, $email)->onQueue('reports');

                return response()->json(['message' => 'System is generating you report. You will be notified once completed'], 200);
            } else {
                return response()->json(['message' => 'Cannot use sync queue driver for export'], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Export failed: '.$e->getMessage());

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function downloadExportedFile($id)
    {
        $exportDetail = ExportDetail::find($id);

        if (! $exportDetail) {
            return redirect()->back()->with('fails', \Lang::get('File not found.'));
        }

        $expirationTime = $exportDetail->created_at->addHours(6);
        if (now()->gt($expirationTime)) {
            return redirect()->back()->with('fails', \Lang::get('This download link has expired'));
        }

        $filePath = $exportDetail->file_path;
        if (! file_exists($filePath)) {
            return redirect()->back()->with('fails', \Lang::get('File not found'));
        }

        $zipFileName = $exportDetail->file.'.zip';
        $zipFilePath = storage_path('app/public/export/'.$zipFileName);
        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            if (is_dir($filePath)) {
                // Add directory and its files to the zip
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath), \RecursiveIteratorIterator::LEAVES_ONLY);
                foreach ($files as $name => $file) {
                    if (! $file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($exportDetail->file_path) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            } else {
                $zip->addFile($filePath, basename($filePath));
            }
            $zip->close();
        } else {
            return redirect()->back()->with('fails', \Lang::get('Failed to create zip file'));
        }

        return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function saveColumns(Request $request)
    {
        $userId = auth()->id();
        $entityType = $request->entity_type;
        $selectedColumns = $request->selected_columns;
        $selectedColumns = array_unique(array_merge($selectedColumns, ['checkbox', 'action']));

        $reportColumns = ReportColumn::whereIn('key', $selectedColumns)
                                 ->where('type', $entityType)
                                 ->pluck('id', 'key');

        UserLinkReport::where('user_id', $userId)->where('type', $entityType)->delete();

        foreach ($selectedColumns as $columnKey) {
            if (isset($reportColumns[$columnKey])) {
                UserLinkReport::create([
                    'user_id' => $userId,
                    'column_id' => $reportColumns[$columnKey],
                    'type' => $entityType,
                ]);
            }
        }

        return response()->json(['message' => 'Columns saved successfully.']);
    }

    public function getColumns(Request $request)
    {
        $userId = auth()->id();
        $entityType = $request->entity_type;

        $userColumns = UserLinkReport::where('user_id', $userId)->where('type', $entityType)->pluck('column_id');
        if ($userColumns->isEmpty()) {
            $defaultColumns = ReportColumn::where('type', $entityType)->where('default', true)->pluck('key');

            return response()->json(['selected_columns' => $defaultColumns]);
        } else {
            $defaultColumns = ReportColumn::where('type', $entityType)->whereIn('id', $userColumns)
                                   ->pluck('key');
        }

        return response()->json(['selected_columns' => $defaultColumns]);
    }
}
