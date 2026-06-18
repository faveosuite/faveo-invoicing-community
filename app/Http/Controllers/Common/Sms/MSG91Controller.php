<?php

namespace App\Http\Controllers\Common\Sms;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\Msg91Status;
use App\Model\Common\MsgDeliveryReports;
use App\Model\Common\StatusSetting;
use App\ThirdPartyApp;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Log;
use Logger;

class MSG91Controller extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (StatusSetting::value('msg91_status') != 1) {
                return redirect()->to('settings')->with('fails', __('message.sms_service_disabled'));
            }

            return $next($request);
        });

        $this->middleware(['auth', 'admin'])->except('handleReports');
    }

    /**
     * Handle MSG91 webhook delivery reports.
     */
    public function handleReports(Request $request, $app_key, $app_secret): void
    {
        if (! $this->validateThirdPartyRequest($app_key, $app_secret)) {
            return;
        }

        try {
            $jsonData = $request->input('data');

            if (empty($jsonData)) {
                Log::warning('MSG91 webhook received empty data', $request->all());

                return;
            }

            // Ensure data is an array
            $reports = is_string($jsonData) ? collect(json_decode($jsonData, associative: true)) : collect($jsonData);

            $reports->each(function (array $reportGroup): void {
                collect($reportGroup['report'])->each(function (array $singleReport) use ($reportGroup): void {
                    $this->processIndividualReport([
                        'request_id' => $reportGroup['requestId'],
                        'number' => $singleReport['number'],
                        'status' => $singleReport['status'],
                        'date' => Date::parse($singleReport['date'], 'Asia/Kolkata')->timezone('UTC')->toDateTimeString(),
                        'failure_reason' => $singleReport['failedReason'] ?? null,
                    ]);
                });
            });
        } catch (Exception $exception) {
            Logger::exception($exception);

            return;
        }
    }

    /**
     * Process and store individual report.
     */
    protected function processIndividualReport(array $reportData): void
    {
        $record = MsgDeliveryReports::where('request_id', $reportData['request_id'])
            ->where('status', 0)
            ->oldest()
            ->first();

        if ($record) {
            $record->update([
                'status' => $reportData['status'],
                'date' => $reportData['date'],
                'failure_reason' => $reportData['failure_reason'],
            ]);
        }
    }

    public function updateOtpRequest(?string $requestId, int $status, string $country_iso, string $mobile, string $mobile_code, ?int $userID = null, ?string $source = null, ?string $action = null): void
    {
        $attributes = [
            'user_id' => $userID,
            'status' => $status,
            'country_iso' => $country_iso,
            'mobile_number' => $mobile,
            'mobile_code' => $mobile_code,
            'source' => $source,
            'action' => $action,
        ];

        if ($requestId) {
            MsgDeliveryReports::updateOrCreate(
                ['request_id' => $requestId],
                $attributes
            );
        } else {
            MsgDeliveryReports::create($attributes);
        }
    }

    /**
     * Create a new retry row for the OTP request.
     *
     * Each retry gets its own row so webhook delivery status is tracked independently.
     */
    public function appendOtpRetry(array $response, string $countryIso, string $mobile, string $mobileCode, int $userID, string $source): void
    {
        $requestId = $response['body']['request_id'] ?? null;

        if (! $requestId) {
            $requestId = MsgDeliveryReports::where('user_id', $userID)
                ->where('source', $source)
                ->latest()
                ->value('request_id');
        }

        if (! $requestId) {
            return;
        }

        $retryCount = MsgDeliveryReports::where('request_id', $requestId)
            ->where('action', 'like', 'retry_%')
            ->count() + 1;

        MsgDeliveryReports::create([
            'request_id' => $requestId,
            'status' => 0,
            'country_iso' => $countryIso,
            'mobile_number' => $mobile,
            'mobile_code' => $mobileCode,
            'user_id' => $userID,
            'source' => $source,
            'action' => 'retry_'.$retryCount,
        ]);
    }

    public function msg91Reports(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $status = Msg91Status::orderBy('status_label')->get();
        $sources = MsgDeliveryReports::query()
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->select('source')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');
        $actions = MsgDeliveryReports::query()
            ->whereNotNull('action')
            ->where('action', '!=', '')
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('themes.default1.common.sms.msgReports', compact('status', 'sources', 'actions')); // @phpstan-ignore argument.type
    }

    public function getMsg91Reports(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortField = $request->input('sort-field', 'created_at');
            $sortOrder = $request->input('sort-order', 'desc');
            $limit = $request->input('limit', 10);

            $baseQuery = $this->msgLogData();

            // Search filter
            if (! empty($searchString)) {
                $baseQuery->where(function ($q) use ($searchString): void {
                    $q->where('request_id', 'like', sprintf('%%%s%%', $searchString))
                        ->orWhere('mobile_number', 'like', sprintf('%%%s%%', $searchString))
                        ->orWhereHas('readableStatus', fn ($q) => $q->where('status_label', 'like', sprintf('%%%s%%', $searchString)))
                        ->orWhereHas('user', function ($sub) use ($searchString): void {
                            $sub->where('email', 'like', sprintf('%%%s%%', $searchString))
                                ->orWhere('user_name', 'like', sprintf('%%%s%%', $searchString))
                                ->orWhere('first_name', 'like', sprintf('%%%s%%', $searchString))
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [sprintf('%%%s%%', $searchString)]);
                        });
                });
            }

            // Apply filter query
            $baseQuery = $this->filterQueryForMsg($baseQuery);

            $total = $baseQuery->count();

            $logs = $baseQuery->orderBy($sortField, $sortOrder)
                    ->simplePaginate($limit);

            // Format collection
            $logs->getCollection()->transform(function (\App\Model\Common\MsgDeliveryReports $log): array {
                $fullName = $log->user ? trim($log->user->first_name.' '.$log->user->last_name) : null;

                return [
                    'request_id' => $log->request_id,
                    'user_id' => $log->user?->id,
                    'user_fullname' => $fullName ?: null,
                    'user_email' => $log->user?->email,
                    'status' => $log->readableStatus?->status_label,
                    'status_code' => $log->status,
                    'source' => $log->source,
                    'action' => $log->action,
                    'failure_reason' => $log->failure_reason,
                    'mobile_number' => $log->mobile_number,
                    'delivery_date' => $log->date,
                    'created_at' => $log->created_at?->format('Y-m-d H:i'),
                ];
            });

            return successResponse(__('message.msg91_reports_fetched'), $logs);
        } catch (Exception) {
            return errorResponse(__('message.something_went_wrong_try_again'));
        }
    }

    public function getMsgFilters(): \Illuminate\Http\JsonResponse
    {
        try {
            $statuses = Msg91Status::orderBy('status_label')->pluck('status_label')->filter()->values();
            $sources = MsgDeliveryReports::whereNotNull('source')->where('source', '!=', '')
                ->distinct()->orderBy('source')->pluck('source');
            $actions = MsgDeliveryReports::whereNotNull('action')->where('action', '!=', '')
                ->distinct()->orderBy('action')->pluck('action');

            return successResponse('', compact('statuses', 'sources', 'actions'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Model\Common\MsgDeliveryReports>
     */
    public function msgLogData(): \Illuminate\Database\Eloquent\Builder
    {
        return MsgDeliveryReports::with(['user:id,user_name,first_name,last_name,email', 'readableStatus']);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Model\Common\MsgDeliveryReports>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Model\Common\MsgDeliveryReports>
     */
    private function filterQueryForMsg(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        $request = request();

        $from = $request->input('date_from');
        $till = $request->input('date_to');

        return $query
            // Request ID Filter
            ->when($request->filled('request_id'), function ($q) use ($request): void {
                $q->where('request_id', 'like', '%'.$request->request_id.'%');
            })

            // Full Name Filter
            ->when($request->filled('full_name'), function ($q) use ($request): void {
                $q->whereHas('user', function ($subQuery) use ($request): void {
                    $subQuery->whereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ['%'.$request->full_name.'%']);
                });
            })

            // Email Filter
            ->when($request->filled('email'), function ($q) use ($request): void {
                $q->whereHas('user', function ($subQuery) use ($request): void {
                    $subQuery->where('email', 'like', '%'.$request->email.'%');
                });
            })

            // Mobile Number Filter
            ->when($request->filled('mobile_number'), function ($q) use ($request): void {
                $q->when($request->filled('country_iso'), function ($q) use ($request): void {
                    $q->where('country_iso', $request->country_iso);
                })->where('mobile_number', 'like', '%'.$request->mobile_number.'%');
            })

            // Status Filter
            ->when($request->filled('status'), function ($q) use ($request): void {
                $q->whereHas('readableStatus', function ($subQuery) use ($request): void {
                    $subQuery->where('status_label', 'like', '%'.$request->status.'%');
                });
            })

            // Source Filter
            ->when($request->filled('source'), function ($q) use ($request): void {
                $q->where('source', 'like', '%'.$request->source.'%');
            })

            // Action Filter
            ->when($request->filled('action'), function ($q) use ($request): void {
                $q->where('action', 'like', '%'.$request->action.'%');
            })

            // Failure Reason Filter
            ->when($request->filled('failure_reason'), function ($q) use ($request): void {
                $q->where('failure_reason', 'like', '%'.$request->failure_reason.'%');
            })

            // Date Range Filter (with safe logic)
            ->when($from || $till, function ($q) use ($from, $till): void {
                $from = $from
                    ? Date::parse($from)->startOfDay()
                    : CarbonImmutable::startOfTime();

                $till = $till
                    ? Date::parse($till)->endOfDay()
                    : Date::now();

                if ($from->lessThanOrEqualTo($till)) {
                    $q->whereBetween('created_at', [$from, $till]);
                }
            });
    }

    public function validateThirdPartyRequest(string $app_key, string $app_secret): bool
    {
        $app = ThirdPartyApp::where('app_key', $app_key)
            ->where('app_secret', $app_secret)
            ->first();

        if (! $app) {
            return false;
        }

        return ApiKey::where('msg91_third_party_id', $app->id)->exists();
    }
}
