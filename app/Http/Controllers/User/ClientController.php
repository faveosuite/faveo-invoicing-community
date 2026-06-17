<?php

namespace App\Http\Controllers\User;

use App\Comment;
use App\ExportDetail;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Requests\User\ClientRequest;
use App\Jobs\AddUserToExternalService;
use App\Jobs\ReportExport;
use App\Model\Common\Bussiness;
use App\Model\Common\Country;
use App\Model\Common\Setting;
use App\Model\Common\State;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Mailjob\QueueService;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Product\Product;
use App\Model\User\AccountActivate;
use App\ReportColumn;
use App\Traits\PaginationTotal;
use App\Traits\PaymentsAndInvoices;
use App\User;
use App\UserLinkReport;
use Auth;
use Carbon\CarbonImmutable;
use DB;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Lang;
use Log;
use Logger;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Str;
use Swift_TransportException;
use ZipArchive;

class ClientController extends AdvanceSearchController
{
    use PaginationTotal;
    use PaymentsAndInvoices;

    /**
     * @var \App\User
     */
    public $user;

    /**
     * @var \App\Model\User\AccountActivate
     */
    public $activate;

    /**
     * @var \App\Model\Product\Product
     */
    public $product;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $user = new User();
        $this->user = $user;

        $activate = new AccountActivate();
        $this->activate = $activate;

        $product = new Product();
        $this->product = $product;
    }

    public function getActiveLabel($mobileActive, $emailActive, $twoFaActive): string
    {
        $emailLabel = "<i class='fas fa-envelope'  style='color:red'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top'  title='".Lang::get('message.unverified_email')."'> </label></i>";
        $mobileLabel = "<i class='fas fa-phone'  style='color:red'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='".Lang::get('message.unverified_mobile')."' >  </label></i>";
        $twoFalabel = "<i class='fas fa-qrcode'  style='color:red'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='".Lang::get('message.2fa_not_enabled')."'> </label></i>";
        if ($mobileActive) {
            $mobileLabel = "<i class='fas fa-phone'  style='color:green'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='".Lang::get('message.mobile_verified')."'></label></i>";
        }

        if ($emailActive) {
            $emailLabel = "<i class='fas fa-envelope'  style='color:green'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='".Lang::get('message.email_verified')."'> </label></i>";
        }

        if ($twoFaActive) {
            $twoFalabel = "<i class='fas fa-qrcode'  style='color:green'  <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title= '".Lang::get('message.2fa_enabled')."'> </label></i>";
        }

        return $emailLabel.'&nbsp;&nbsp;'.$mobileLabel.'&nbsp;&nbsp;'.$twoFalabel;
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
            $password = Hash::make($str);
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
                'email_verified' => $request->input('active'),
                'mobile_verified' => $request->input('mobile_verified'),
                'mobile_country_iso' => $request->input('mobile_country_iso'),
                'company_type' => $request->input('company_type'),
                'company_size' => $request->input('company_size'),
                'address' => $request->input('address'),
                'town' => $request->input('town'),
                'country' => strtoupper((string) $request->input('country')),
                'state' => $request->input('state'),
                'zip' => $request->input('zip'),
                'timezone_id' => $request->input('timezone_id'),
                'mobile_code' => $mobile_code,
                'mobile' => $request->input('mobile'),
                'skype' => $request->input('skype'),
                'ip' => $location['ip'],
            ];

            $userInput = User::create($user);
            $userInput->active = 1;
            $userInput->role = $request->input('role');
            $userInput->position = $request->input('position');
            $userInput->manager = $request->input('manager');
            $userInput->account_manager = $request->input('account_manager');
            $userInput->save();

            if (emailSendingStatus()) {
                $this->sendWelcomeMail($userInput);
            }

            dispatch(new AddUserToExternalService($userInput));

            return back()->with('success', \Lang::get('message.saved-successfully'));
        } catch (Swift_TransportException $e) {
            return back()->with('warning',
                __('message.user_created_but_email_problem').$e->getMessage());
        } catch (Exception $e) {
            return back()->with('fails', $e->getMessage());
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
            $user->fill($request->input());
            $user->role = $request->input('role', $user->role);
            $user->active = $request->input('active', $user->active);
            $user->position = $request->input('position', $user->position);
            $user->manager = $request->input('manager', $user->manager);
            $user->account_manager = $request->input('account_manager', $user->account_manager);
            $user->save();

            // \Session::put('test', 1000);
            return back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return back()->with('fails', $exception->getMessage());
        }
    }

    public function sendWelcomeMail($user): void
    {
        // Retrieve necessary data
        $contact = getContactData();
        $settings = Setting::find(1);
        $template_type = TemplateType::where('name', 'registration_mail')->first();
        $template = Template::find($template_type->id);

        // Check if settings or template is missing
        if (! $settings || ! $template) {
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

        $type = $template->type ? TemplateType::find($template->type)->name : '';

        // Send the email
        $mail = new PhpMailController();
        $mail->SendEmail($settings->email, $user->email, $template->data, $template->name, $template->type()->value('name'), $replace, $type);
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
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                DB::raw("CONCAT('+', users.mobile_code, ' ', users.mobile) as mobile"),
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as name"),
                'countries.country_name as country',
                'users.created_at',
                'users.active',
                'users.mobile_verified',
                'users.email_verified',
                'users.is_2fa_enabled',
                'users.role',
                'users.position'
            );
        // Apply other conditions based on the request
        $baseQuery = $baseQuery->when($request->company, function ($query) use ($request): void {
            $query->where('company', 'LIKE', '%'.$request->company.'%');
        })->when($request->country, function ($query) use ($request): void {
            $query->where('users.country', $request->country); // or 'countries.country_name'
        })->when($request->industry, function ($query) use ($request): void {
            $query->where('bussiness', $request->industry);
        })->when($request->role, function ($query) use ($request): void {
            $query->where('role', $request->role);
        })->when($request->position, function ($query) use ($request): void {
            $query->where('position', $request->position);
        })->when($request->actmanager, function ($query) use ($request): void {
            $query->where('account_manager', $request->actmanager);
        })->when($request->salesmanager, function ($query) use ($request): void {
            $query->where('manager', $request->salesmanager);
        })->when($request->filled('mobile_verified'), function ($query) use ($request): void {
            $query->where('mobile_verified', $request->mobile_verified);
        })->when($request->filled('email_verified'), function ($query) use ($request): void {
            $query->where('email_verified', $request->email_verified);
        })->when($request->filled('is_2fa_enabled'), function ($query) use ($request): void {
            $query->where('is_2fa_enabled', $request->is_2fa_enabled);
        });

        return $this->getregFromTill($baseQuery, $request->reg_from, $request->reg_till);
    }

    public function exportUsers(Request $request)
    {
        try {
            ini_set('memory_limit', '-1');

            $selectedColumns = $request->input('selected_columns', []);
            $searchParams = $request->input('search_params', []);
            $email = Auth::user()->email;

            $driver = QueueService::where('status', '1')->first();

            if ($driver->name === 'Sync') {
                return errorResponse(__('message.cannot_sync_queue_driver'));
            }

            // Set the queue driver dynamically
            resolve('queue')->setDefaultDriver($driver->short_name);

            dispatch(new ReportExport('users', $selectedColumns, $searchParams, $email))
                ->onQueue('reports');

            return successResponse(__('message.system_generating_report'));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return errorResponse($exception->getMessage());
        }
    }

    public function downloadExportedFile($id)
    {
        try {
            $exportDetail = ExportDetail::findOrFail($id);

            if (! $exportDetail) {
                return errorResponse(__('message.file_not_found'));
            }

            $expirationTime = $exportDetail->created_at->addHours(6);
            if (now()->gt($expirationTime)) {
                return errorResponse(__('message.download_link_expired'));
            }

            $filePath = $exportDetail->file_path;
            if (! file_exists($filePath)) {
                return errorResponse(__('message.file_not_found'));
            }

            $zipFileName = $exportDetail->file.'.zip';
            $zipFilePath = storage_path('app/public/export/'.$zipFileName);
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                if (is_dir($filePath)) {
                    // Add directory and its files to the zip
                    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($filePath), RecursiveIteratorIterator::LEAVES_ONLY);
                    foreach ($files as $file) {
                        if (! $file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = substr((string) $filePath, strlen((string) $exportDetail->file_path) + 1);
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                } else {
                    $zip->addFile($filePath, basename((string) $filePath));
                }

                $zip->close();
            } else {
                return errorResponse(__('message.failed_create_zip_file'));
            }

            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(shouldDelete: true);
        } catch (Exception $exception) {
            Log::error('Report Export Failure'.$exception->getMessage());

            return errorResponse('Report Export Failure'.$exception->getMessage());
        }
    }

    public function saveColumns(Request $request)
    {
        $userId = auth()->id();
        $entityType = $request->get('entity_type');
        $selectedKeys = $request->get('selected_columns', []);

        // Always ensure the locked checkbox & action columns exist, while
        // preserving the incoming display order (drag-and-drop reordering).
        if (! in_array('checkbox', $selectedKeys, strict: true)) {
            array_unshift($selectedKeys, 'checkbox');
        }

        if (! in_array('action', $selectedKeys, strict: true)) {
            $selectedKeys[] = 'action';
        }

        // De-dupe while keeping the first occurrence's position.
        $selectedKeys = array_values(array_unique($selectedKeys));

        // Map column keys to IDs
        $reportColumns = ReportColumn::where('type', $entityType)
            ->whereIn('key', $selectedKeys)
            ->pluck('id', 'key');

        UserLinkReport::where('user_id', $userId)
            ->where('type', $entityType)
            ->delete();

        $insertData = [];
        $order = 1;
        foreach ($selectedKeys as $key) {
            if (isset($reportColumns[$key])) {
                $insertData[] = [
                    'user_id' => $userId,
                    'column_id' => $reportColumns[$key],
                    'type' => $entityType,
                    'order' => $order++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if ($insertData !== []) {
            UserLinkReport::insert($insertData);
        }

        return successResponse(__('message.columns_saved_successfully.'), [
            'selected_columns' => $selectedKeys,
        ]);
    }

    public function getColumns(Request $request)
    {
        $userId = auth()->id();
        $entityType = $request->get('entity_type');

        // All available columns for this entity, in their canonical order.
        $allColumns = ReportColumn::where('type', $entityType)
            ->orderBy('id')
            ->get(['id', 'key', 'label', 'default']);

        // The user's saved selection + display order (keyed by column id).
        $saved = UserLinkReport::where('user_id', $userId)
            ->where('type', $entityType)
            ->orderBy('order')
            ->get(['column_id', 'order'])
            ->keyBy('column_id');

        $hasSaved = $saved->isNotEmpty();

        // Merge availability with the user's preference. New columns that the
        // user has never seen sink to the bottom and stay hidden by default.
        $columns = $allColumns->map(function ($col) use ($saved, $hasSaved): array {
            $savedRow = $saved->get($col->id);

            return [
                'id' => $col->id,
                'key' => $col->key,
                'label' => $col->label,
                'is_visible' => $hasSaved ? (bool) $savedRow : (bool) $col->default,
                'order' => $savedRow->order ?? (1000 + $col->id),
            ];
        })->sortBy('order')->values();

        return successResponse('', [
            // Kept flat for the legacy blade lists (they read selected keys only).
            'selected_columns' => $columns->where('is_visible', true)->pluck('key')->values(),
            // Full ordered metadata consumed by the Vue ColumnSelector.
            'columns' => $columns,
        ]);
    }

    public function getAllUsers(Request $request)
    {
        $searchQuery = $request->input('search-query', '');
        $sortOrder = $request->input('sort-order', 'desc');
        $sortField = $request->input('sort-field', 'created_at');
        $limit = $request->input('limit', 10);

        $query = User::select('id', 'first_name', 'last_name', 'email', 'mobile', 'country', 'created_at', 'email_verified', 'mobile_verified', 'is_2fa_enabled');

        $total = $this->cachedTotal($query, $request, [
            'company', 'country', 'industry', 'role', 'position',
            'actmanager', 'salesmanager', 'mobile_verified', 'email_verified',
            'is_2fa_enabled', 'reg_from', 'reg_till',
        ]);

        $query = $this->applyUsersFilters($query, $request);

        $query = $this->applyUsersSearch($query, $searchQuery);

        $users = $query
            ->orderBy($sortField, $sortOrder)
            ->simplePaginate($limit);

        $users->getCollection()->transform(function ($user) {
            if ($user->country) {
                $name = getCountryByCode($user->country) ?? $user->country;
                $user->setRawAttributes(array_merge($user->getAttributes(), ['country' => $name]), true);
            }

            return $user;
        });

        return $this->paginateResponse($users, $total);
    }

    public function deleteBulkUsers(Request $request)
    {
        $ids = $request->input('user_ids', []);

        if (empty($ids)) {
            return errorResponse(__('message.select-a-row'));
        }

        $accountManagers = User::whereIn('id', $ids)
            ->where('position', 'account_manager')
            ->get(['first_name', 'last_name']);

        $salesManagers = User::whereIn('id', $ids)
            ->where('position', 'manager')
            ->get(['first_name', 'last_name']);

        if ($accountManagers->isNotEmpty() || $salesManagers->isNotEmpty()) {
            $usersInfo = collect([
                'account_manager' => $accountManagers,
                'sales_manager' => $salesManagers,
            ])
                ->flatMap(fn ($collection, $role) => $collection->map(fn ($u): string => $u->first_name.' '.$u->last_name.' ('.__('message.' . $role).')'))
                ->implode(', ');

            return errorResponse(__('message.deletion_blocked', [
                'names' => $usersInfo,
            ]));
        }

        User::whereIn('id', $ids)->get()->each->delete();

        return successResponse(__('message.user-suspend-successfully'));
    }

    public function userCreate(ClientRequest $request)
    {
        try {
            $password = Hash::make(Str::password(12));

            $mobile_code = str_replace('+', '', $request->input('mobile_code') ??
                Country::where('country_code_char2', $request->input('country'))->value('phonecode'));

            $location = getLocation();

            $userData = $request->only([
                'user_name', 'first_name', 'last_name', 'email', 'company',
                'bussiness', 'role', 'position', 'mobile_country_iso',
                'company_type', 'company_size', 'address', 'town', 'state',
                'zip', 'timezone_id', 'mobile', 'skype', 'manager', 'account_manager',
            ]);

            $userData = array_merge($userData, [
                'password' => $password,
                'active' => 1,
                'email_verified' => $request->boolean('active'),
                'mobile_verified' => $request->boolean('mobile_verified'),
                'country' => strtoupper((string) $request->input('country')),
                'mobile_code' => $mobile_code,
                'ip' => $location['ip'] ?? null,
            ]);

            $user = User::create($userData);

            if (emailSendingStatus()) {
                $this->sendWelcomeMail($user);
            }

            dispatch(new AddUserToExternalService($user));

            return successResponse(__('message.user-create-successfully'), $user);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getEditUser($id)
    {
        $user = User::with([
            'timezone',
            'manager:id,first_name,last_name,email',
            'accountManager:id,first_name,last_name,email',
        ])->find($id);

        if (! $user) {
            return errorResponse(__('message.user_not_found'), 404);
        }

        $bussinessShort = $user->attributes['bussiness'] ?? null;
        $bussinessObj = null;
        if ($bussinessShort) {
            $b = Bussiness::where('short', $bussinessShort)->first();
            $bussinessObj = $b ? ['id' => $b->short, 'name' => $b->name] : null;
        }

        $countryObj = null;
        if ($user->country) {
            $c = Country::where('country_code_char2', $user->country)->first();
            $countryObj = $c ? ['id' => $c->country_id, 'name' => $c->country_name, 'code' => $c->country_code_char2] : null;
        }

        $stateObj = null;
        if ($user->state) {
            $s = State::find($user->state);
            $stateObj = $s ? ['id' => $s->state_subdivision_id, 'name' => $s->state_subdivision_name] : null;
        }

        $timezoneObj = $user->timezone
            ? ['id' => $user->timezone->id, 'name' => $user->timezone->timezone_name]
            : null;

        $mgr = $user->manager instanceof User ? $user->manager : null;
        $managerObj = $mgr instanceof \App\User ? [
            'id' => $mgr->id,
            'name' => trim($mgr->first_name.' '.$mgr->last_name),
            'email' => $mgr->email,
        ] : null;

        $acm = $user->accountManager instanceof User ? $user->accountManager : null;
        $accountManagerObj = $acm instanceof \App\User ? [
            'id' => $acm->id,
            'name' => trim($acm->first_name.' '.$acm->last_name),
            'email' => $acm->email,
        ] : null;

        return successResponse('', [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => trim($user->first_name.' '.$user->last_name),
            'email' => $user->email,
            'user_name' => $user->user_name,
            'profile_pic' => $user->profile_pic,
            'company' => $user->company ?? '',
            'bussiness' => $bussinessObj,
            'active' => $user->active ?? 1,
            'email_verified' => $user->email_verified ?? 0,
            'mobile_verified' => $user->mobile_verified ?? 0,
            'is_2fa_enabled' => $user->is_2fa_enabled ?? 0,
            'role' => $user->role,
            'position' => $user->position,
            'company_type' => $user->attributes['company_type'] ?? null,
            'company_size' => $user->attributes['company_size'] ?? null,
            'address' => $user->address ?? '',
            'town' => $user->town ?? '',
            'country' => $countryObj,
            'state' => $stateObj,
            'zip' => $user->zip ?? '',
            'timezone_id' => $timezoneObj,
            'mobile' => $user->mobile ?? '',
            'mobile_code' => $user->mobile_code ?? '',
            'mobile_country_iso' => $user->mobile_country_iso ?? '',
            'skype' => $user->skype ?? '',
            'manager' => $managerObj,
            'account_manager' => $accountManagerObj,
        ]);
    }

    public function userUpdate($id, ClientRequest $request)
    {
        try {
            $user = User::find($id);

            if (! $user) {
                return errorResponse(__('message.user_not_found'), 404);
            }

            $user->fill($request->all());

            $user->save();

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getUserSummary($id)
    {
        try {
            $user = User::find($id);
            if (! $user) {
                return errorResponse(__('message.user_not_found'), 404);
            }

            $invoices = Invoice::where('user_id', $id)->get();
            $invoiceSum = $this->getTotalInvoice($invoices);
            $amountPaid = $this->getAmountPaid($id);
            $balance = $invoiceSum - $amountPaid;
            $currency = getCurrencyForClient($user->country);

            return successResponse('', [
                'invoice_total' => $invoiceSum,
                'amount_paid' => $amountPaid,
                'balance' => $balance,
                'currency' => $currency,
                'invoice_count' => $invoices->count(),
                'payment_count' => Payment::where('user_id', $id)->count(),
                'order_count' => Order::where('client', $id)->count(),
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getUserInvoices($id, Request $request)
    {
        try {
            $limit = $request->input('limit', 15);
            $page = $request->input('page', 1);
            $sortField = $request->input('sort-field', 'date');
            $sortOrder = $request->input('sort-order', 'desc');

            $allowedSorts = ['date', 'number', 'grand_total', 'status'];
            if (! in_array($sortField, $allowedSorts, strict: true)) {
                $sortField = 'date';
            }

            $invoices = Invoice::where('user_id', $id)
                ->orderBy($sortField, $sortOrder)
                ->paginate($limit, ['*'], 'page', $page);

            $invoices->getCollection()->transform(function ($invoice): array {
                $paid = Payment::where('invoice_id', $invoice->id)->sum('amount');
                $balance = max(0, $invoice->grand_total - $paid);

                return [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'date' => $invoice->date,
                    'grand_total' => $invoice->grand_total,
                    'paid' => $paid,
                    'balance' => $balance,
                    'currency' => $invoice->currency,
                    'status' => $invoice->status,
                ];
            });

            return successResponse('', $invoices);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getUserPayments($id, Request $request)
    {
        try {
            $limit = $request->input('limit', 15);
            $page = $request->input('page', 1);
            $sortField = $request->input('sort-field', 'created_at');
            $sortOrder = $request->input('sort-order', 'desc');

            $allowedSorts = ['created_at', 'amount', 'payment_method', 'payment_status'];
            if (! in_array($sortField, $allowedSorts, strict: true)) {
                $sortField = 'created_at';
            }

            $payments = Payment::where('user_id', $id)
                ->orderBy($sortField, $sortOrder)
                ->paginate($limit, ['*'], 'page', $page);

            $payments->getCollection()->transform(function ($payment): array {
                $invoice = $payment->invoice_id ? Invoice::find($payment->invoice_id) : null;

                return [
                    'id' => $payment->id,
                    'invoice_id' => $payment->invoice_id,
                    'invoice_number' => $invoice?->number,
                    'date' => $payment->created_at,
                    'payment_method' => $payment->payment_method,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency ?: $invoice?->currency,
                    'status' => $payment->payment_status,
                ];
            });

            return successResponse('', $payments);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function getUserComments($id)
    {
        try {
            $comments = Comment::with('user:id,first_name,last_name')
                ->where('user_id', $id)->latest()
                ->get()
                ->map(fn ($c): array => [
                    'id' => $c->id,
                    'description' => $c->description,
                    'created_at' => $c->created_at,
                    'updated_at' => $c->updated_at,
                    'author' => $c->user
                        ? trim($c->user->first_name.' '.$c->user->last_name)
                        : null,
                ]);

            return successResponse('', $comments);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function storeUserComment($id, Request $request)
    {
        try {
            $user = User::find($id);
            if (! $user) {
                return errorResponse(__('message.user_not_found'), 404);
            }

            $comment = Comment::create([
                'user_id' => $id,
                'updated_by_user_id' => auth()->id(),
                'description' => $request->input('description'),
            ]);

            return successResponse(__('message.saved-successfully'), [
                'id' => $comment->id,
                'description' => $comment->description,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                'author' => trim(auth()->user()->first_name.' '.auth()->user()->last_name),
            ]);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function updateUserComment($id, $commentId, Request $request)
    {
        try {
            $comment = Comment::where('id', $commentId)->where('user_id', $id)->firstOrFail();
            $comment->description = $request->input('description');
            $comment->updated_by_user_id = auth()->id();
            $comment->save();

            return successResponse(__('message.updated-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function deleteUserComment($id, $commentId)
    {
        try {
            Comment::where('id', $commentId)->where('user_id', $id)->firstOrFail()->delete();

            return successResponse(__('message.deleted-successfully'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    private function applyUsersFilters($query, Request $request)
    {
        return $query
            ->when($request->filled('company'), fn ($q) => $q->where('company', 'like', '%'.$request->company.'%')
            )
            ->when($request->filled('country'), fn ($q) => $q->where('country', $request->country)
            )
            ->when($request->filled('industry'), fn ($q) => $q->where('bussiness', $request->industry)
            )
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role)
            )
            ->when($request->filled('position'), fn ($q) => $q->where('position', $request->position)
            )
            ->when($request->filled('actmanager'), fn ($q) => $q->where('account_manager', $request->actmanager)
            )
            ->when($request->filled('salesmanager'), fn ($q) => $q->where('manager', $request->salesmanager)
            )
            ->when($request->filled('mobile_verified'), fn ($q) => $q->where('mobile_verified', $request->mobile_verified)
            )
            ->when($request->filled('email_verified'), fn ($q) => $q->where('email_verified', $request->email_verified)
            )
            ->when($request->filled('is_2fa_enabled'), fn ($q) => $q->where('is_2fa_enabled', $request->is_2fa_enabled)
            )
            ->when($request->hasAny(['reg_from', 'reg_till']), function ($q) use ($request): void {
                $from = $request->filled('reg_from')
                    ? Date::parse($request->input('reg_from'))->startOfDay()
                    : CarbonImmutable::startOfTime();

                $till = $request->filled('reg_till')
                    ? Date::parse($request->input('reg_till'))->endOfDay()
                    : Date::now()->endOfDay();

                $q->whereBetween('created_at', [$from, $till]);
            });
    }

    private function applyUsersSearch($query, $search)
    {
        return $query->when($search, function ($q) use ($search): void {
            $q->where(function ($subQuery) use ($search): void {
                $subQuery->where('email', 'like', '%'.$search.'%')
                    ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', '%'.$search.'%')
                    ->orWhere('mobile', 'like', '%'.$search.'%')
                    ->orWhere('country', 'like', '%'.$search.'%');
            });
        });
    }
}
