<?php

namespace App\Http\Controllers\Report;

use App\ExportDetail;
use App\Exports\InvoiceExport;
use App\Exports\OrderExport;
use App\Exports\TenatExport;
use App\Exports\UsersExport;
use App\Http\Controllers\Order\OrderSearchController;
use App\Model\Common\Country;
use App\Model\Common\FaveoCloud;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\ReportSetting;
use App\ThirdPartyApp;
use App\Traits\CoupCodeAndInvoiceSearch;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

abstract class ExportHandleController
{
    use CoupCodeAndInvoiceSearch;
    protected $reportType;
    protected $selectedColumns;
    protected $searchParams;
    protected $email;

    public function __construct($reportType, $selectedColumns, $searchParams, $email)
    {
        $this->reportType = $reportType;
        $this->selectedColumns = $selectedColumns;
        $this->searchParams = $searchParams;
        $this->email = $email;
    }

    abstract public function userExports($selectedColumns, $searchParams, $email);

    abstract public function invoiceExports($selectedColumns, $searchParams, $email);

    abstract public function orderExports($selectedColumns, $searchParams, $email);

    abstract public function tenantExports($selectedColumns, $searchParams, $email);
}

class ConcreteExportHandleController extends ExportHandleController
{
    public function __construct($reportType, $selectedColumns, $searchParams, $email)
    {
        parent::__construct($reportType, $selectedColumns, $searchParams, $email);
    }

    public function userExports($selectedColumns, $searchParams, $email)
    {
        try {
            // Filter out unwanted columns
            $selectedColumns = array_filter($selectedColumns, function ($column) {
                return ! in_array($column, ['checkbox', 'action']);
            });

            // Prepare the query
            $users = User::query();

            $statusColumns = ['mobile_verified', 'active', 'is_2fa_enabled'];
            foreach ($statusColumns as $statusColumn) {
                if (! in_array($statusColumn, $selectedColumns)) {
                    $selectedColumns[] = $statusColumn;
                }
            }

            // Apply search filters
            foreach ($searchParams as $key => $value) {
                if ($value !== null && $value !== '') {
                    if ($key === 'reg_from') {
                        $users->whereDate('created_at', '>=', date('Y-m-d', strtotime($value)));
                    } elseif ($key === 'reg_till') {
                        $users->whereDate('created_at', '<=', date('Y-m-d', strtotime($value)));
                    } else {
                        switch ($key) {
                            case 'company':
                                $users->where('company', 'LIKE', '%'.$value.'%');
                                break;
                            case 'country':
                                $users->where('country', $value);
                                break;
                            case 'industry':
                                $users->where('bussiness', $value);
                                break;
                            case 'role':
                                $users->where('role', $value);
                                break;
                            case 'position':
                                $users->where('position', $value);
                                break;
                            case 'actmanager':
                                $users->where('account_manager', $value);
                                break;
                            case 'salesmanager':
                                $users->where('manager', $value);
                                break;
                            case 'mobile_verified':
                                $users->where('mobile_verified', $value);
                                break;
                            case 'active':
                                $users->where('active', $value);
                                break;
                            case 'is_2fa_enabled':
                                $users->where('is_2fa_enabled', $value);
                                break;
                            default:
                                $users->where($key, $value);
                                break;
                        }
                    }
                }
            }

            $users->orderBy('created_at', 'desc');

            // Ensure status columns are included
            if (! empty($selectedColumns) && $selectedColumns == 'active') {
                $statusColumns = ['mobile_verified', 'active', 'is_2fa_enabled'];
                foreach ($statusColumns as $statusColumn) {
                    if (! in_array($statusColumn, $selectedColumns)) {
                        $selectedColumns[] = $statusColumn;
                    }
                }
            }

            // Use LazyCollection for efficient memory usage
            $filteredUsers = $users->lazy()->map(function ($user) use ($selectedColumns) {
                $userData = [];
                foreach ($selectedColumns as $column) {
                    switch ($column) {
                        case 'name':
                            $userData['name'] = $user->first_name.' '.$user->last_name;
                            break;
                        case 'mobile':
                            $userData['mobile'] = '+'.$user->mobile_code.' '.$user->mobile;
                            break;
                        case 'mobile_verified':
                        case 'active':
                        case 'is_2fa_enabled':
                            $userData[$column] = $user->$column ? 'Active' : 'Inactive';
                            break;
                        case 'created_at':
                            $userData['created_at'] = \Carbon\Carbon::parse($user->created_at)->format('Y-m-d');
                            break;
                        case 'country':
                            $country = Country::where('country_code_char2', $user->country)->value('nicename');
                            $userData['country'] = $country;
                            break;
                        default:
                            $userData[$column] = $user->$column;
                    }
                }

                return $userData;
            });

            if ($filteredUsers->isEmpty()) {
                return response()->json(['message' => __('message.no_data_available_export')], 400);
            }

            // Get the report setting for the record limit
            $limit = ReportSetting::first()->value('records');
            $chunks = $filteredUsers->chunk($limit);

            // Get user details for email
            $id = User::where('email', $email)->value('id');
            $user = User::find($id);
            $timestamp = now()->format('Ymd_His');
            $folderName = 'users_export_'.$id.'_'.$timestamp.'_XLSX';
            $folderPath = storage_path('app/public/export/'.$folderName);

            // Create directory if it doesn't exist
            if (! file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }

            // Process and store each chunk
            foreach ($chunks as $index => $chunk) {
                $export = new UsersExport($selectedColumns, $chunk, $index + 1);
                $fileName = 'users_'.$id.'_part'.($index + 1).'.xlsx';
                $filePath = $folderPath.'/'.$fileName;
                Excel::store($export, 'public/export/'.$folderName.'/'.$fileName);
            }

            // Save export details
            $exportDetail = ExportDetail::create([
                'user_id' => $id,
                'file_path' => $folderPath,
                'file' => $folderName,
                'name' => 'users',
            ]);

            // Send email notification
            $settings = \App\Model\Common\Setting::find(1);
            $from = $settings->email;
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $downloadLink = route('download.exported.file', ['id' => $exportDetail->id]);
            $emailContent = 'Hello '.$user->first_name.' '.$user->last_name.','.
                '<br><br>User report is successfully generated and ready for download.'.
                '<br><br>Download link: <a href="'.$downloadLink.'">'.$downloadLink.'</a>'.
                '<br><br>Please note this link will be expired in 6 hours.'.
                '<br><br>Kind regards,<br>'.$user->first_name;

            $mail->SendEmail($from, $email, $emailContent, 'User report available for download', 'user-report');

            return response()->json(['message' => __('message.report_email_generated')], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => __('message.failed_generate_report').$ex->getMessage()], 500);
        }
    }

    public function invoiceExports($selectedColumns, $searchParams, $email)
    {
        try {
            // Filter out unwanted columns
            $selectedColumns = array_filter($selectedColumns, function ($column) {
                return ! in_array($column, ['checkbox', 'action']);
            });

            // Perform search and filtering
            $request = new Request();
            $request->merge($searchParams);
            $name = $request->input('name');
            $invoice_no = $request->input('invoice_no');
            $status = $request->input('status');
            $currency = $request->input('currency_id');
            $from = $request->input('from');
            $till = $request->input('till');

            // Get invoices with filters applied
            $invoices = $this->advanceSearch($name, $invoice_no, $currency, $status, $from, $till);
            $invoices->orderBy('date', 'desc');

            // Use LazyCollection for efficient memory usage
            $filteredInvoices = $invoices->lazy()->map(function ($invoice) use ($selectedColumns) {
                $invoiceData = [];
                foreach ($selectedColumns as $column) {
                    switch ($column) {
                        case 'user_id':
                            $user = $invoice->user;
                            $invoiceData['name'] = $user ? $user->first_name.' '.$user->last_name : null;
                            break;
                        case 'email':
                            $invoiceData['email'] = $user ? $user->email : null;
                            break;
                        case 'mobile':
                            $invoiceData['mobile'] = $user ? '+'.$user->mobile_code.' '.$user->mobile : null;
                            break;
                        case 'country':
                            if ($user) {
                                $country = Country::where('country_code_char2', $user->country)->value('nicename');
                                $invoiceData['country'] = $country ? $country : null;
                            } else {
                                $invoiceData['country'] = null;
                            }
                            break;

                        case 'grand_total':
                            $invoiceData['total'] = currencyFormat($invoice->grand_total, $invoice->currency);
                            break;
                        case 'product':
                            $item = InvoiceItem::where('invoice_id', $invoice->id)->first();
                            $invoiceData['product'] = $item ? $item->product_name : null;
                            break;
                        case 'date':
                            $invoiceData['date'] = \Carbon\Carbon::parse($invoice->created_at)->format('Y-m-d');
                            break;
                        case 'status':
                            $invoiceData['status'] = $this->getStatus($invoice->status);
                            break;
                        default:
                            $invoiceData[$column] = $invoice->$column;
                    }
                }

                return $invoiceData;
            });

            if ($filteredInvoices->isEmpty()) {
                return response()->json(['message' => __('message.no_data_available_export')], 400);
            }

            // Get user details for email
            $id = User::where('email', $email)->value('id');
            $user = User::find($id);
            $timestamp = now()->format('Ymd_His');
            $folderName = 'invoices_export_'.$id.'_'.$timestamp.'_XLSX';
            $folderPath = storage_path('app/public/export/'.$folderName);

            // Create directory if it doesn't exist
            if (! file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }

            // Get the report setting for the record limit
            $limit = ReportSetting::first()->value('records');
            $chunks = $filteredInvoices->chunk($limit);

            // Process and store each chunk
            foreach ($chunks as $index => $chunk) {
                $export = new InvoiceExport($selectedColumns, $chunk, $index + 1);
                $fileName = 'invoices_'.$id.'_part'.($index + 1).'.xlsx';
                $filePath = $folderPath.'/'.$fileName;
                Excel::store($export, 'public/export/'.$folderName.'/'.$fileName);
            }

            // Save export details
            $exportDetail = ExportDetail::create([
                'user_id' => $id,
                'file_path' => $folderPath,
                'file' => $folderName,
                'name' => 'invoices',
            ]);

            // Send email notification
            $settings = \App\Model\Common\Setting::find(1);
            $from = $settings->email;
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $downloadLink = route('download.exported.file', ['id' => $exportDetail->id]);
            $emailContent = 'Hello '.$user->first_name.' '.$user->last_name.','.
                '<br><br>Invoice report is successfully generated and ready for download.'.
                '<br><br>Download link: <a href="'.$downloadLink.'">'.$downloadLink.'</a>'.
                '<br><br>Please note this link will be expired in 6 hours.'.
                '<br><br>Kind regards,<br>'.$user->first_name;

            $mail->SendEmail($from, $email, $emailContent, 'Invoice report available for download', 'invoice-report');

            return response()->json(['message' => __('message.report_email_generated')], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => __('message.failed_generate_report').$ex->getMessage()], 500);
        }
    }

    public function orderExports($selectedColumns, $searchParams, $email)
    {
        try {
            // Filter out unwanted columns
            $selectedColumns = array_filter($selectedColumns, function ($column) {
                return ! in_array($column, ['checkbox', 'action']);
            });
            $searchRequest = new Request($searchParams);

            // Perform advanced order search
            $orderSearch = new OrderSearchController();
            $orders = $orderSearch->advanceOrderSearch($searchRequest);

            $orders->orderBy('orders.created_at', 'desc');

            // Use LazyCollection for efficient memory usage
            $filteredOrders = $orders->lazy()->map(function ($order) use ($selectedColumns) {
                $orderData = [];
                foreach ($selectedColumns as $column) {
                    switch ($column) {
                        case 'client':
                            $orderData['name'] = $order->client_name;
                            break;
                        case 'email':
                            $orderData['email'] = $order->email;
                            break;
                        case 'mobile':
                            $orderData['mobile'] = $order->mobile;
                            break;
                        case 'country':
                            if ($order) {
                                $country = Country::where('country_code_char2', $order->country)->value('nicename');
                                $orderData['country'] = $country ? $country : null;
                            } else {
                                $orderData['country'] = null;
                            }
                            break;
                        case 'status':
                            $orderData['status'] = $order->installation_path ? 'Active' : 'Inactive';
                            break;
                        case 'product_name':
                            $orderData['product_name'] = $order->product_name;
                            break;
                        case 'plan_name':
                            $plan = Plan::find($order->plan_id);
                            $orderData['plan_name'] = $plan ? $plan->name : 'Unknown Plan';
                            break;
                        case 'version':
                            $orderData['version'] = $order->product_version;
                            break;
                        case 'agents':
                            $orderData['agents'] = $this->getAgents($order);
                            break;
                        case 'order_date':
                            $orderData['order_date'] = \Carbon\Carbon::parse($order->subscription_created_at)->format('Y-m-d');
                            break;
                        case 'update_ends_at':
                            $orderData['update_ends_at'] = \Carbon\Carbon::parse($order->subscription_ends_at)->format('Y-m-d');
                            break;
                        default:
                            $orderData[$column] = $order->$column;
                    }
                }

                return $orderData;
            });

            if ($filteredOrders->isEmpty()) {
                throw new \Exception(__('message.no_data_available_export'));
            }

            // Get user details for email
            $id = User::where('email', $email)->value('id');
            $user = User::find($id);
            $timestamp = now()->format('Ymd_His');
            $folderName = 'orders_export_'.$id.'_'.$timestamp.'_XLSX';
            $folderPath = storage_path('app/public/export/'.$folderName);

            // Create directory if it doesn't exist
            if (! file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }

            // Get the report setting for the record limit
            $limit = ReportSetting::first()->value('records');
            $chunks = $filteredOrders->chunk($limit);

            // Process and store each chunk
            foreach ($chunks as $index => $chunk) {
                $export = new OrderExport($selectedColumns, $chunk, $index + 1);
                $fileName = 'orders_'.$id.'_part'.($index + 1).'.xlsx';
                $filePath = $folderPath.'/'.$fileName;
                Excel::store($export, 'public/export/'.$folderName.'/'.$fileName);
            }

            // Create ExportDetail record
            $exportDetail = ExportDetail::create([
                'user_id' => $id,
                'file_path' => $folderPath,
                'file' => $folderName,
                'name' => 'orders',
            ]);

            // Send email notification
            $settings = \App\Model\Common\Setting::find(1);
            $from = $settings->email;
            $mail = new \App\Http\Controllers\Common\PhpMailController();
            $downloadLink = route('download.exported.file', ['id' => $exportDetail->id]);
            $emailContent = 'Hello '.$user->first_name.' '.$user->last_name.','.
                '<br><br>Order report is successfully generated and ready for download.'.
                '<br><br>Download link: <a href="'.$downloadLink.'">'.$downloadLink.'</a>'.
                '<br><br>Please note this link will expire in 6 hours.'.
                '<br><br>Kind regards,<br>'.$user->first_name;

            $mail->SendEmail($from, $email, $emailContent, 'Order report available for download', 'order-report');

            return response()->json(['message' => __('message.report_email_generated')], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => __('message.failed_generate_report').$ex->getMessage()], 500);
        }
    }

    public function tenantExports($selectedColumns, $searchParams, $email)
    {
        $this->cloud = FaveoCloud::first();
        $client = new Client();

        // Similar logic to export users but for orders
        $this->selectedColumns = array_filter($this->selectedColumns, function ($column) {
            return ! in_array($column, ['action']);
        });

        $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();

        if (! $keys->app_key) {
            // Validate if the app key to be sent is valid or not
            throw new \Exception(__('message.cloud_invalid_message'));
        }

        $response = $client->request(
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

        $tenats = collect($responseData->message)->reject(function ($item) {
            return $item === null;
        });
        $filteredTenants = $tenats->map(function ($tenats) {
            $tenantData = [];
            foreach ($this->selectedColumns as $column) {
                switch ($column) {
                    case 'Order':
                        $order_id = \DB::table('installation_details')->where('installation_path', $tenats->domain)->latest()->value('order_id');
                        $order_number = \DB::table('orders')->where('id', $order_id)->value('number');
                        $tenantData['Order'] = $order_number;
                        break;
                    case 'name':
                        $order_id = \DB::table('installation_details')
                                    ->where('installation_path', $tenats->domain)
                                    ->latest()
                                    ->value('order_id');

                        if (! $order_id) {
                            $tenantData['name'] = null;
                        } else {
                            $userId = Order::where('id', $order_id)->value('client');
                            if (! $userId) {
                                $tenantData['name'] = null;
                            } else {
                                $user = User::find($userId);
                                if (! $user) {
                                    $tenantData['name'] = null;
                                } else {
                                    $tenantData['name'] = $user->first_name.' '.$user->last_name;
                                }
                            }
                        }
                        break;

                    case 'email':
                        $order_id = \DB::table('installation_details')
                                    ->where('installation_path', $tenats->domain)
                                    ->latest()
                                    ->value('order_id');

                        if (! $order_id) {
                            $tenantData['email'] = null;
                        } else {
                            $userId = Order::where('id', $order_id)->value('client');
                            if (! $userId) {
                                $tenantData['email'] = null;
                            } else {
                                $user = User::find($userId);
                                if (! $user) {
                                    $tenantData['email'] = null;
                                } else {
                                    $tenantData['email'] = $user->email;
                                }
                            }
                        }
                        break;

                    case 'mobile':
                        $order_id = \DB::table('installation_details')
                                    ->where('installation_path', $tenats->domain)
                                    ->latest()
                                    ->value('order_id');

                        if (! $order_id) {
                            $tenantData['mobile'] = null;
                        } else {
                            $userId = Order::where('id', $order_id)->value('client');
                            if (! $userId) {
                                $tenantData['mobile'] = null;
                            } else {
                                $user = User::find($userId);
                                if (! $user) {
                                    $tenantData['mobile'] = null;
                                } else {
                                    $tenantData['mobile'] = $user->mobile;
                                }
                            }
                        }
                        break;

                    case 'country':
                        $order_id = \DB::table('installation_details')
                                    ->where('installation_path', $tenats->domain)
                                    ->latest()
                                    ->value('order_id');

                        if (! $order_id) {
                            $tenantData['country'] = null;
                        } else {
                            $userId = Order::where('id', $order_id)->value('client');
                            if (! $userId) {
                                $tenantData['country'] = null;
                            } else {
                                $user = User::find($userId);
                                if (! $user) {
                                    $tenantData['country'] = null;
                                } else {
                                    $country = Country::where('country_code_char2', $user->country)->value('nicename');
                                    $tenantData['country'] = $country;
                                }
                            }
                        }
                        break;

                    case 'Expiry day':
                        $order_id = \DB::table('installation_details')
                                    ->where('installation_path', $tenats->domain)
                                    ->latest()
                                    ->value('order_id');
                        $subscription_date = Subscription::where('order_id', $order_id)->value('ends_at');
                        if (empty($subscription_date)) {
                            $tenantData['Expiry day'] = null;
                        } else {
                            $tenantData['Expiry day'] = Carbon::parse($subscription_date)->format('d M Y');
                        }
                        break;

                    case 'Deletion day':
                        $order_id = \DB::table('installation_details')
                                    ->where('installation_path', $tenats->domain)
                                    ->latest()
                                    ->value('order_id');
                        $subscription_date = Subscription::where('order_id', $order_id)->value('ends_at');
                        if (empty($subscription_date)) {
                            $tenantData['Deletion day'] = null;
                        } else {
                            $days = \DB::table('expiry_mail_days')->where('cloud_days', '!=', null)->value('cloud_days');
                            $originalDate = Carbon::parse($subscription_date)->addDays($days);
                            $formattedDate = Carbon::parse($originalDate)->format('d M Y');
                            $tenantData['Deletion day'] = $formattedDate;
                        }
                        break;

                    case 'plan':
                        $order_id = \DB::table('installation_details')
                                    ->where('installation_path', $tenats->domain)
                                    ->latest()
                                    ->value('order_id');
                        if (empty($order_id)) {
                            $tenantData['plan'] = null;
                        } else {
                            $plan_id = Subscription::where('order_id', $order_id)->latest()->value('plan_id');
                            $price = PlanPrice::where('plan_id', $plan_id)->latest()->value('add_price');
                            $message = ($price) ? 'Paid Subscription' : 'Free Trial';
                            $tenantData['plan'] = $message;
                        }
                        break;

                    case 'tenants':
                        $order_id = \DB::table('installation_details')->where('installation_path', $tenats->domain)->latest()->value('order_id');
                        $order_number = \DB::table('orders')->where('id', $order_id)->value('number');
                        $tenantData['tenats'] = $tenats->id;
                        break;

                    case 'domain':
                        $tenantData['domain'] = $tenats->domain;
                        break;

                    case 'db_name':
                        $tenantData['db_name'] = $tenats->database_name;
                        break;

                    case 'db_username':
                        $tenantData['db_username'] = $tenats->database_user_name;
                        break;
                    default:
                        $tenantData[$column] = $tenant->$column ?? null;
                        break;
                }
            }

            return $tenantData;
        });

        if ($filteredTenants->isEmpty()) {
            throw new \Exception(__('message.no_data_available_export'));
        }
        // Get user details for email
        $id = User::where('email', $email)->value('id');
        $user = User::find($id);
        $timestamp = now()->format('Ymd_His');
        $folderName = 'tenants_export_'.$id.'_'.$timestamp.'_XLSX';
        $folderPath = storage_path('app/public/export/'.$folderName);

        // Create directory
        if (! file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        // Get the report setting for the record limit
        $limit = ReportSetting::first()->value('records');
        $chunks = $filteredTenants->chunk($limit);

        foreach ($chunks as $index => $chunk) {
            $export = new TenatExport($this->selectedColumns, $chunk, $index + 1);
            $fileName = 'tenants_'.$id.'_part'.($index + 1).'.xlsx';
            $filePath = $folderPath.'/'.$fileName;
            Excel::store($export, 'public/export/'.$folderName.'/'.$fileName);
        }

        $exportDetail = ExportDetail::create([
            'user_id' => $id,
            'file_path' => $folderPath,
            'file' => $folderName,
            'name' => 'tenants',
        ]);

        $settings = new \App\Model\Common\Setting();
        $setting = $settings::find(1);
        $from = $setting->email;
        $mail = new \App\Http\Controllers\Common\PhpMailController();
        $downloadLink = route('download.exported.file', ['id' => $exportDetail->id]);
        $emailContent = 'Hello '.$user->first_name.' '.$user->last_name.','.
            '<br><br>Tenant report is successfully generated and ready for download.'.
            '<br><br>Download link: <a href="'.$downloadLink.'">'.$downloadLink.'</a>'.
            '<br><br>Please note this link will be expired in 6 hours.'.
            '<br><br>Kind regards,<br>'.$user->first_name;

        $mail->SendEmail($from, $this->email, $emailContent, 'Tenant report available for download', 'tenant-report');
    }

    public function getStatus($status)
    {
        switch ($status) {
            case 'Pending':
                return 'unpaid';
            case 'Success':
                return 'paid';
            case 'Renewed':
                return 'renewed';
            default:
                return 'partially paid';
        }
    }

    public function allInstallations($allInstallation, $orders)
    {
        if ($allInstallation) {
            $dayUtc = new Carbon('-30 days');
            $minus30Day = $dayUtc->toDateTimeString();

            switch ($allInstallation) {
                case 'installed':
                    return $orders->whereColumn('subscriptions.created_at', '!=', 'subscriptions.updated_at');
                case 'not_installed':
                    return $orders->whereColumn('subscriptions.created_at', '=', 'subscriptions.updated_at');
                case 'paid_inactive_ins':
                    return $orders->where('subscriptions.updated_at', '<', $minus30Day);
                case 'paid_ins':
                    return $orders->whereColumn('subscriptions.created_at', '!=', 'subscriptions.updated_at')
                                  ->where('subscriptions.updated_at', '>', $minus30Day);
                default:
                    return $orders;
            }
        }
    }

    public function getSelectedVersionOrders($baseQuery, $version, $productId, $request)
    {
        if ($version) {
            if ($productId == 'paid' || $productId == 'unpaid') {
                $latestVersion = ProductUpload::orderBy('version', 'desc')->value('version');
                if ($version == 'Latest') {
                    $baseQuery->where('subscriptions.version', '=', $latestVersion);
                } elseif ($version == 'Outdated') {
                    $baseQuery->where('subscriptions.version', '<', $latestVersion);
                }
            } elseif ($version == 'Outdated') {
                $latestVersion = Subscription::where('product_id', $productId)
                                             ->orderBy('version', 'desc')
                                             ->value('version');
                $baseQuery->where('subscriptions.version', '!=', null)
                          ->where('subscriptions.version', '!=', '')
                          ->where('subscriptions.version', '<', $latestVersion);
            } else {
                $baseQuery->where('subscriptions.version', '=', $version);
            }
        }

        return $baseQuery;
    }

    public function getAgents($order)
    {
        $license = substr($order->serial_key, 12, 16);
        if ($license == '0000') {
            return 'Unlimited';
        }

        return intval($license, 10);
    }
}
