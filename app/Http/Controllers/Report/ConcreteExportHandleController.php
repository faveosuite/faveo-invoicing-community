<?php

namespace App\Http\Controllers\Report;

use App\ExportDetail;
use App\Exports\InvoiceExport;
use App\Exports\OrderExport;
use App\Exports\TenatExport;
use App\Exports\UsersExport;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\Order\OrderSearchController;
use App\Model\Common\Country;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Setting;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Payment\PlanPrice;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\ReportSetting;
use App\ThirdPartyApp;
use App\Traits\CoupCodeAndInvoiceSearch;
use App\User;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Facades\Excel;

abstract class ExportHandleController
{
    use CoupCodeAndInvoiceSearch;

    /**
     * @param  array<mixed>  $searchParams
     * @param  array<mixed>  $selectedColumns
     */
    public function __construct(protected string $reportType, protected array $selectedColumns, protected array $searchParams, protected string $email)
    {
    }

    /**
     * @param  array<mixed>  $selectedColumns
     * @param  array<mixed>  $searchParams
     */
    abstract public function userExports(array $selectedColumns, array $searchParams, string $email): JsonResponse;

    /**
     * @param  array<mixed>  $selectedColumns
     * @param  array<mixed>  $searchParams
     */
    abstract public function invoiceExports(array $selectedColumns, array $searchParams, string $email): JsonResponse;

    /**
     * @param  array<mixed>  $selectedColumns
     * @param  array<mixed>  $searchParams
     */
    abstract public function orderExports(array $selectedColumns, array $searchParams, string $email): JsonResponse;

    /**
     * @param  array<mixed>  $selectedColumns
     * @param  array<mixed>  $searchParams
     */
    abstract public function tenantExports(array $selectedColumns, array $searchParams, string $email): void;
}

class ConcreteExportHandleController extends ExportHandleController
{
    /**
     * @param  array<mixed>  $searchParams
     * @param  array<mixed>  $selectedColumns
     * @param  array<mixed>  $searchParams
     * @param  array<mixed>  $selectedColumns
     */
    public function userExports(array $selectedColumns, array $searchParams, string $email): JsonResponse
    {
        try {
            // Filter out unwanted columns
            $selectedColumns = array_filter($selectedColumns, fn ($column): bool => ! in_array($column, ['checkbox', 'action']));

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
                        $time = strtotime((string) $value);
                        $date = $time !== false ? date('Y-m-d', $time) : date('Y-m-d');
                        $users->where('created_at', '>=', Date::parse($date)->startOfDay());
                    } elseif ($key === 'reg_till') {
                        $time = strtotime((string) $value);
                        $date = $time !== false ? date('Y-m-d', $time) : date('Y-m-d');
                        $users->where('created_at', '<=', Date::parse($date)->endOfDay());
                    } else {
                        match ($key) {
                            'company' => $users->where('company', 'LIKE', '%'.$value.'%'),
                            'country' => $users->where('country', $value),
                            'industry' => $users->where('bussiness', $value),
                            'role' => $users->where('role', $value),
                            'position' => $users->where('position', $value),
                            'actmanager' => $users->where('account_manager', $value),
                            'salesmanager' => $users->where('manager', $value),
                            'mobile_verified' => $users->where('mobile_verified', $value),
                            'active' => $users->where('active', $value),
                            'is_2fa_enabled' => $users->where('is_2fa_enabled', $value),
                            default => $users->where($key, $value),
                        };
                    }
                }
            }

            $users->latest();

            // Ensure status columns are included
            if ($selectedColumns == 'active') {
                $statusColumns = ['mobile_verified', 'active', 'is_2fa_enabled'];
                foreach ($statusColumns as $statusColumn) {
                    if (! in_array($statusColumn, $selectedColumns)) {
                        $selectedColumns[] = $statusColumn;
                    }
                }
            }

            // Use LazyCollection for efficient memory usage
            $filteredUsers = $users->lazy()->map(function ($user) use ($selectedColumns): array {
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
                            $userData['created_at'] = Date::parse($user->created_at)->format('Y-m-d');
                            break;
                        case 'country':
                            $country = Country::where('country_code_char2', $user->country)->value('country_name');
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
            $reportSetting = ReportSetting::first();
            $limit = $reportSetting ? (int) $reportSetting->records : 1000;
            $chunks = $filteredUsers->chunk($limit);

            // Get user details for email
            $user = User::where('email', $email)->first();
            if (! $user instanceof User) {
                return response()->json(['message' => 'User not found.'], 404);
            }
            $id = $user->id;
            $timestamp = now()->format('Ymd_His');
            $folderName = 'users_export_'.$id.'_'.$timestamp.'_XLSX';
            $folderPath = storage_path('app/public/export/'.$folderName);

            // Create directory if it doesn't exist
            if (! file_exists($folderPath)) {
                mkdir($folderPath, 0777, recursive: true);
            }

            // Process and store each chunk
            foreach ($chunks as $index => $chunk) {
                $export = new UsersExport($selectedColumns, $chunk, $index + 1);
                $fileName = 'users_'.$id.'_part'.($index + 1).'.xlsx';

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
            $settings = Setting::find(1);
            if (! $settings instanceof Setting) {
                return response()->json(['message' => 'Setting not found.'], 404);
            }
            $from = $settings->email;
            $mail = new PhpMailController;
            $downloadLink = route('download.exported.file', ['id' => $exportDetail->id]);
            $emailContent = 'Hello '.$user->first_name.' '.$user->last_name.','.
                '<br><br>User report is successfully generated and ready for download.'.
                '<br><br>Download link: <a href="'.$downloadLink.'">'.$downloadLink.'</a>'.
                '<br><br>Please note this link will be expired in 6 hours.'.
                '<br><br>Kind regards,<br>Team '.$settings->title;

            $mail->SendEmail($from, $email, $emailContent, 'User report available for download', 'user-report');

            return response()->json(['message' => __('message.report_email_generated')], 200);
        } catch (Exception $exception) {
            return response()->json(['message' => __('message.failed_generate_report').$exception->getMessage()], 500);
        }
    }

    /**
     * @param  array<mixed>  $searchParams
     * @param  array<mixed>  $selectedColumns
     * @param  array<mixed>  $searchParams
     * @param  array<mixed>  $selectedColumns
     */
    public function invoiceExports(array $selectedColumns, array $searchParams, string $email): JsonResponse
    {
        try {
            // Filter out unwanted columns
            $selectedColumns = array_filter($selectedColumns, fn ($column): bool => ! in_array($column, ['checkbox', 'action']));

            // Perform search and filtering
            $request = new Request;
            $request->merge($searchParams);

            // Get invoices with filters applied
            $invoices = $this->advanceSearch($request);
            $invoices->orderBy('date', 'desc');

            // Use LazyCollection for efficient memory usage
            $filteredInvoices = $invoices->lazy()->map(function ($invoice) use ($selectedColumns): array {
                $invoiceData = [];
                $user = null;
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
                                $country = Country::where('country_code_char2', $user->country)->value('country_name');
                                $invoiceData['country'] = $country ?: null;
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
                            $invoiceData['date'] = Date::parse($invoice->created_at)->format('Y-m-d');
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
            $user = User::where('email', $email)->first();
            if (! $user instanceof User) {
                return response()->json(['message' => 'User not found.'], 404);
            }
            $id = $user->id;
            $timestamp = now()->format('Ymd_His');
            $folderName = 'invoices_export_'.$id.'_'.$timestamp.'_XLSX';
            $folderPath = storage_path('app/public/export/'.$folderName);

            // Create directory if it doesn't exist
            if (! file_exists($folderPath)) {
                mkdir($folderPath, 0777, recursive: true);
            }

            // Get the report setting for the record limit
            $reportSetting = ReportSetting::first();
            $limit = $reportSetting ? (int) $reportSetting->records : 1000;
            $chunks = $filteredInvoices->chunk($limit);

            // Process and store each chunk
            foreach ($chunks as $index => $chunk) {
                $export = new InvoiceExport($selectedColumns, $chunk, $index + 1);
                $fileName = 'invoices_'.$id.'_part'.($index + 1).'.xlsx';

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
            $settings = Setting::find(1);
            if (! $settings instanceof Setting) {
                return response()->json(['message' => 'Setting not found.'], 404);
            }
            $from = $settings->email;
            $mail = new PhpMailController;
            $downloadLink = route('download.exported.file', ['id' => $exportDetail->id]);
            $emailContent = 'Hello '.$user->first_name.' '.$user->last_name.','.
                '<br><br>Invoice report is successfully generated and ready for download.'.
                '<br><br>Download link: <a href="'.$downloadLink.'">'.$downloadLink.'</a>'.
                '<br><br>Please note this link will be expired in 6 hours.'.
                '<br><br>Kind regards,<br>Team '.$settings->title;

            $mail->SendEmail($from, $email, $emailContent, 'Invoice report available for download', 'invoice-report');

            return response()->json(['message' => __('message.report_email_generated')], 200);
        } catch (Exception $exception) {
            return response()->json(['message' => __('message.failed_generate_report').$exception->getMessage()], 500);
        }
    }

    /**
     * @param  array<mixed>  $searchParams
     * @param  array<mixed>  $selectedColumns
     * @param  array<mixed>  $searchParams
     * @param  array<mixed>  $selectedColumns
     */
    public function orderExports(array $selectedColumns, array $searchParams, string $email): JsonResponse
    {
        try {
            // Filter out unwanted columns
            $selectedColumns = array_filter($selectedColumns, fn ($column): bool => ! in_array($column, ['checkbox', 'action']));
            $searchRequest = new Request($searchParams);

            // Perform advanced order search
            $orderSearch = new OrderSearchController;
            $orders = $orderSearch->advanceOrderSearch($searchRequest);

            $orders->latest('orders.created_at');

            // Use LazyCollection for efficient memory usage
            $filteredOrders = $orders->lazy()->map(function (Order $order) use ($selectedColumns): array {
                $orderData = [];
                $orderUser = $order->user;
                foreach ($selectedColumns as $column) {
                    switch ($column) {
                        case 'client':
                            $orderData['name'] = $orderUser
                                ? $orderUser->first_name.' '.$orderUser->last_name
                                : null;
                            break;
                        case 'email':
                            $orderData['email'] = $orderUser?->email;
                            break;
                        case 'mobile':
                            $orderData['mobile'] = $orderUser?->mobile;
                            break;
                        case 'country':
                            $country = $orderUser?->country
                                ? Country::where('country_code_char2', $orderUser->country)->value('country_name')
                                : null;
                            $orderData['country'] = $country ?: null;

                            break;
                        case 'status':
                            $orderData['status'] = $order->installationDetails->isNotEmpty() ? 'Active' : 'Inactive';
                            break;
                        case 'product_name':
                            $orderData['product_name'] = $order->productRelation?->name;
                            break;
                        case 'plan_name':
                            $plan = $order->subscription?->plan;
                            $orderData['plan_name'] = $plan ? $plan->name : 'Unknown Plan';
                            break;
                        case 'group_name':
                            $orderData['group_name'] = $order->productRelation?->groupRelation?->name;
                            break;
                        case 'version':
                            $orderData['version'] = $order->subscription?->version;
                            break;
                        case 'agents':
                            $orderData['agents'] = $this->getAgents($order);
                            break;
                        case 'order_date':
                            $orderData['order_date'] = $order->subscription
                                ? Date::parse($order->subscription->created_at)->format('Y-m-d')
                                : null;
                            break;
                        case 'update_ends_at':
                            $orderData['update_ends_at'] = $order->subscription
                                ? Date::parse($order->subscription->ends_at)->format('Y-m-d')
                                : null;
                            break;
                        default:
                            $orderData[$column] = $order->$column;
                    }
                }

                return $orderData;
            });

            if ($filteredOrders->isEmpty()) {
                throw new Exception(__('message.no_data_available_export'));
            }

            // Get user details for email
            $user = User::where('email', $email)->first();
            if (! $user instanceof User) {
                return response()->json(['message' => 'User not found.'], 404);
            }
            $id = $user->id;
            $timestamp = now()->format('Ymd_His');
            $folderName = 'orders_export_'.$id.'_'.$timestamp.'_XLSX';
            $folderPath = storage_path('app/public/export/'.$folderName);

            // Create directory if it doesn't exist
            if (! file_exists($folderPath)) {
                mkdir($folderPath, 0777, recursive: true);
            }

            // Get the report setting for the record limit
            $reportSetting = ReportSetting::first();
            $limit = $reportSetting ? (int) $reportSetting->records : 1000;
            $chunks = $filteredOrders->chunk($limit);

            // Process and store each chunk
            foreach ($chunks as $index => $chunk) {
                $export = new OrderExport($selectedColumns, $chunk, $index + 1);
                $fileName = 'orders_'.$id.'_part'.($index + 1).'.xlsx';

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
            $settings = Setting::find(1);
            if (! $settings instanceof Setting) {
                return response()->json(['message' => 'Setting not found.'], 404);
            }
            $from = $settings->email;
            $mail = new PhpMailController;
            $downloadLink = route('download.exported.file', ['id' => $exportDetail->id]);
            $emailContent = 'Hello '.$user->first_name.' '.$user->last_name.','.
                '<br><br>Order report is successfully generated and ready for download.'.
                '<br><br>Download link: <a href="'.$downloadLink.'">'.$downloadLink.'</a>'.
                '<br><br>Please note this link will expire in 6 hours.'.
                '<br><br>Kind regards,<br>Team '.$settings->title;

            $mail->SendEmail($from, $email, $emailContent, 'Order report available for download', 'order-report');

            return response()->json(['message' => __('message.report_email_generated')], 200);
        } catch (Exception $exception) {
            return response()->json(['message' => __('message.failed_generate_report').$exception->getMessage()], 500);
        }
    }

    /**
     * @param  array<mixed>  $searchParams
     * @param  array<mixed>  $selectedColumns
     * @param  array<mixed>  $searchParams
     * @param  array<mixed>  $selectedColumns
     */
    public function tenantExports(array $selectedColumns, array $searchParams, string $email): void
    {
        $this->cloud = FaveoCloud::first(); // @phpstan-ignore property.notFound
        if (! $this->cloud) {
            throw new Exception('FaveoCloud configuration not found.');
        }
        $client = new Client;

        // Similar logic to export users but for orders
        $this->selectedColumns = array_filter($this->selectedColumns, fn ($column): bool => $column != 'action');

        $keys = ThirdPartyApp::where('app_name', 'faveo_app_key')->select('app_key', 'app_secret')->first();
        if (! $keys) {
            throw new Exception(__('message.cloud_invalid_message'));
        }

        if (! $keys->app_key) {
            // Validate if the app key to be sent is valid or not
            throw new Exception(__('message.cloud_invalid_message'));
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

        $tenats = collect((array) ($responseData->message ?? []))->reject(fn ($item): bool => $item === null);
        $filteredTenants = $tenats->map(function ($tenats): array {
            $tenantData = [];
            foreach ($this->selectedColumns as $column) {
                switch ($column) {
                    case 'Order':
                        $order_id = DB::table('installation_details')->where('installation_path', $tenats->domain)->latest()->value('order_id');
                        $order_number = DB::table('orders')->where('id', $order_id)->value('number');
                        $tenantData['Order'] = $order_number;
                        break;
                    case 'name':
                        $order_id = DB::table('installation_details')
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
                                $tenantData['name'] = ($user instanceof User) ? $user->first_name.' '.$user->last_name : null;
                            }
                        }

                        break;

                    case 'email':
                        $order_id = DB::table('installation_details')
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
                                $tenantData['email'] = ($user instanceof User) ? $user->email : null;
                            }
                        }

                        break;

                    case 'mobile':
                        $order_id = DB::table('installation_details')
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
                                $tenantData['mobile'] = ($user instanceof User) ? $user->mobile : null;
                            }
                        }

                        break;

                    case 'country':
                        $order_id = DB::table('installation_details')
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
                                if (! $user instanceof User) {
                                    $tenantData['country'] = null;
                                } else {
                                    $country = Country::where('country_code_char2', $user->country)->value('country_name');
                                    $tenantData['country'] = $country;
                                }
                            }
                        }

                        break;

                    case 'Expiry day':
                        $order_id = DB::table('installation_details')
                            ->where('installation_path', $tenats->domain)
                            ->latest()
                            ->value('order_id');
                        $subscription_date = Subscription::where('order_id', $order_id)->value('ends_at');
                        if (empty($subscription_date)) {
                            $tenantData['Expiry day'] = null;
                        } else {
                            $tenantData['Expiry day'] = Date::parse($subscription_date)->format('d M Y');
                        }

                        break;

                    case 'Deletion day':
                        $order_id = DB::table('installation_details')
                            ->where('installation_path', $tenats->domain)
                            ->latest()
                            ->value('order_id');
                        $subscription_date = Subscription::where('order_id', $order_id)->value('ends_at');
                        if (empty($subscription_date)) {
                            $tenantData['Deletion day'] = null;
                        } else {
                            $days = DB::table('expiry_mail_days')->where('cloud_days', '!=', null)->value('cloud_days');
                            $originalDate = Date::parse($subscription_date)->addDays($days);
                            $formattedDate = Date::parse($originalDate)->format('d M Y');
                            $tenantData['Deletion day'] = $formattedDate;
                        }

                        break;

                    case 'plan':
                        $order_id = DB::table('installation_details')
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
                        $order_id = DB::table('installation_details')->where('installation_path', $tenats->domain)->latest()->value('order_id');
                        $order_number = DB::table('orders')->where('id', $order_id)->value('number');
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
                        $tenantData[$column] = $tenats->$column ?? null;
                        break;
                }
            }

            return $tenantData;
        });

        if ($filteredTenants->isEmpty()) {
            throw new Exception(__('message.no_data_available_export'));
        }

        // Get user details for email
        $user = User::where('email', $email)->first();
        if (! $user instanceof User) {
            throw new Exception('User not found.');
        }
        $id = $user->id;
        $timestamp = now()->format('Ymd_His');
        $folderName = 'tenants_export_'.$id.'_'.$timestamp.'_XLSX';
        $folderPath = storage_path('app/public/export/'.$folderName);

        // Create directory
        if (! file_exists($folderPath)) {
            mkdir($folderPath, 0777, recursive: true);
        }

        // Get the report setting for the record limit
        $reportSetting = ReportSetting::first();
        $limit = $reportSetting ? (int) $reportSetting->records : 1000;
        $chunks = $filteredTenants->chunk($limit);

        foreach ($chunks as $index => $chunk) {
            $export = new TenatExport($this->selectedColumns, $chunk, $index + 1);
            $fileName = 'tenants_'.$id.'_part'.($index + 1).'.xlsx';

            Excel::store($export, 'public/export/'.$folderName.'/'.$fileName);
        }

        $exportDetail = ExportDetail::create([
            'user_id' => $id,
            'file_path' => $folderPath,
            'file' => $folderName,
            'name' => 'tenants',
        ]);

        $setting = Setting::find(1);
        if (! $setting instanceof Setting) {
            throw new Exception('Setting not found.');
        }
        $from = $setting->email;
        $mail = new PhpMailController;
        $downloadLink = route('download.exported.file', ['id' => $exportDetail->id]);
        $emailContent = 'Hello '.$user->first_name.' '.$user->last_name.','.
            '<br><br>Tenant report is successfully generated and ready for download.'.
            '<br><br>Download link: <a href="'.$downloadLink.'">'.$downloadLink.'</a>'.
            '<br><br>Please note this link will be expired in 6 hours.'.
            '<br><br>Kind regards,<br>Team '.$setting->title;

        $mail->SendEmail($from, $this->email, $emailContent, 'Tenant report available for download', 'tenant-report');
    }

    public function getStatus(string $status): string
    {
        return match ($status) {
            'Pending' => 'unpaid',
            'Success' => 'paid',
            'Renewed' => 'renewed',
            default => 'partially paid',
        };
    }

    /**
     * @param  Builder<Model>  $orders
     * @return Builder<Model>
     */
    public function allInstallations(?string $allInstallation, Builder $orders): ?Builder
    {
        if ($allInstallation) {
            $dayUtc = new Carbon('-30 days');
            $minus30Day = $dayUtc->toDateTimeString();

            return match ($allInstallation) {
                'installed' => $orders->whereColumn('subscriptions.created_at', '!=', 'subscriptions.updated_at'),
                'not_installed' => $orders->whereColumn('subscriptions.created_at', '=', 'subscriptions.updated_at'),
                'paid_inactive_ins' => $orders->where('subscriptions.updated_at', '<', $minus30Day),
                'paid_ins' => $orders->whereColumn('subscriptions.created_at', '!=', 'subscriptions.updated_at')
                    ->where('subscriptions.updated_at', '>', $minus30Day),
                default => $orders,
            };
        }

        return null;
    }

    /**
     * @param  Builder<Model>  $baseQuery
     * @return Builder<Model>
     */
    public function getSelectedVersionOrders(Builder $baseQuery, ?string $version, string|int $productId): Builder
    {
        if ($version) {
            if ($productId == 'paid' || $productId == 'unpaid') {
                $latestVersion = ProductUpload::orderBy('version', 'desc')->value('version');
                if ($version === 'Latest') {
                    $baseQuery->where('subscriptions.version', '=', $latestVersion);
                } elseif ($version === 'Outdated') {
                    $baseQuery->where('subscriptions.version', '<', $latestVersion);
                }
            } elseif ($version === 'Outdated') {
                $latestVersion = Subscription::where('product_id', $productId)
                    ->orderBy('version', 'desc')
                    ->value('version');
                $baseQuery->where('subscriptions.version', '!=', value: null)
                    ->where('subscriptions.version', '!=', '')
                    ->where('subscriptions.version', '<', $latestVersion);
            } else {
                $baseQuery->where('subscriptions.version', '=', $version);
            }
        }

        return $baseQuery;
    }

    public function getAgents(Order $order): string|int
    {
        $license = substr((string) $order->serial_key, 12, 16);
        if ($license === '0000') {
            return 'Unlimited';
        }

        return intval($license, 10);
    }
}
