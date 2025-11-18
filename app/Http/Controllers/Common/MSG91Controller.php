<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\Msg91Status;
use App\Model\Common\MsgDeliveryReports;
use App\ThirdPartyApp;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MSG91Controller extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Handle MSG91 webhook delivery reports.
     *
     * @param  Request  $request
     */
    public function handleReports(Request $request, $app_key, $app_secret): void
    {
        if (! $this->validateThirdPartyRequest($app_key, $app_secret)) {
            return;
        }
        try {
            $jsonData = $request->input('data');

            if (empty($jsonData)) {
                \Log::warning('MSG91 webhook received empty data', $request->all());

                return;
            }

            // Ensure data is an array
            $reports = is_string($jsonData) ? collect(json_decode($jsonData, true)) : collect($jsonData);

            $reports->each(function ($reportGroup) {
                collect($reportGroup['report'])->each(function ($singleReport) use ($reportGroup) {
                    $this->processIndividualReport([
                        'request_id' => $reportGroup['requestId'],
                        'number' => $singleReport['number'],
                        'status' => $singleReport['status'],
                        'date' => Carbon::parse($singleReport['date'], 'Asia/Kolkata')->timezone('UTC')->toDateTimeString() ?? now()->utc()->toDateTimeString(),
                        'failure_reason' => $singleReport['failedReason'] ?? null,
                    ]);
                });
            });
        } catch (\Exception $e) {
            \Log::error('Error processing MSG91 reports: '.$e->getMessage());

            return;
        }
    }

    /**
     * Process and store individual report.
     *
     * @param  array  $reportData
     */
    protected function processIndividualReport(array $reportData)
    {
        MsgDeliveryReports::where('request_id', $reportData['request_id'])
            ->update([
                'status' => $reportData['status'],
                'date' => $reportData['date'],
                'failure_reason' => $reportData['failure_reason'],
            ]);
    }

    public function updateOtpRequest($requestId, $status, $country_iso, $mobile, $mobile_code, $userID = null)
    {
        MsgDeliveryReports::updateOrCreate(
            ['request_id' => $requestId],
            [
                'user_id' => $userID,
                'status' => $status,
                'country_iso' => $country_iso,
                'mobile_number' => $mobile,
                'mobile_code' => $mobile_code,
            ]
        );
    }

//    public function msg91Reports()
//    {
//        $status = Msg91Status::orderBy('status_label')->get();
//
//        return view('themes.default1.common.sms.msgReports', compact('status'));
//    }

    public function getMsg91Reports(Request $request)
    {
        $this->request = $request;

        $sortField = $request->input('sort_field', 'date');
        $sortOrder = $request->input('sort_order', 'desc');
        $limit = $request->input('limit', 10);

        $baseQuery = $this->msgLogData();
        $baseQuery = $this->searchQuery($baseQuery);
        $baseQuery = $this->filterQuery($baseQuery);

        // Paginate and count
        $logs = $baseQuery->orderBy($sortField, $sortOrder)->simplePaginate($limit);
        $total = $baseQuery->count();

        // Format Output
        $logs->getCollection()->transform(function ($log) {
            $fullName = $log->user ? trim($log->user->first_name.' '.$log->user->last_name) : null;

            return [
                'request_id' => $log->request_id,
                'user_fullname' => $fullName,
                'user_email' => $log->user?->email,
                'status' => $log->readableStatus->status_label,
                'failure_reason' => $log->failure_reason,
                'mobile_number' => $log->mobile_number,
                'delivery_date' => $log->date,
                'created_at' => $log->created_at,
            ];
        });

        return successResponse(__('message.msg91_reports_fetched'), [
            'logs' => $logs,
            'total' => $total,
        ]);
    }

    public function msgLogData()
    {
        return MsgDeliveryReports::with(['user:id,user_name,first_name,last_name,email', 'readableStatus']);
    }

    private function searchQuery($query)
    {
        $search = $this->request->input('search-query');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('request_id', 'like', "%$search%")
                    ->orWhereHas('readableStatus', function ($q) use ($search) {
                        $q->where('status_label', 'like', "%$search%");
                    })
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('email', 'like', "%$search%")
                            ->orWhere('user_name', 'like', "%$search%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                    });
            });
        }

        return $query;
    }

    private function filterQuery($query)
    {
        $request = $this->request;

        // Request ID Filter
        $query->when($request->filled('request_id'), function ($q) use ($request) {
            $q->where('request_id', 'like', '%'.$request->request_id.'%');
        });

        // Full Name Filter
        $query->when($request->filled('full_name'), function ($q) use ($request) {
            $q->whereHas('user', function ($subQuery) use ($request) {
                $subQuery->whereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ['%'.$request->full_name.'%']);
            });
        });

        // Email Filter
        $query->when($request->filled('email'), function ($q) use ($request) {
            $q->whereHas('user', function ($subQuery) use ($request) {
                $subQuery->where('email', 'like', '%'.$request->email.'%');
            });
        });

        // Mobile Number Filter
        $query->when($request->filled('mobile_number'), function ($q) use ($request) {
            $q->when($request->filled('country_iso'), function ($q) use ($request) {
                $q->where('country_iso', $request->country_iso);
            })->where('mobile_number', 'like', '%'.$request->mobile_number.'%');
        });

        // Status Filter
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->whereHas('readableStatus', function ($subQuery) use ($request) {
                $subQuery->where('status_label', 'like', '%'.$request->status.'%');
            });
        });

        // Failure Reason Filter
        $query->when($request->filled('failure_reason'), function ($q) use ($request) {
            $q->where('failure_reason', 'like', '%'.$request->failure_reason.'%');
        });

        // Date Range Filter
        if ($request->filled('log_from') || $request->filled('log_till')) {
            $from = $request->log_from ?: $request->log_till;
            $till = $request->log_till ?: $request->log_from;

            $fromUtc = toFormatDateAndTime($from);
            $tillUtc = toFormatDateAndTime($till);

            $fromUtc = strlen($from) <= 10 ? $fromUtc->startOfDay() : $fromUtc;
            $tillUtc = strlen($till) <= 10 ? $tillUtc->endOfDay() : $tillUtc;

            $query->whereBetween('created_at', [$fromUtc, $tillUtc]);
        }

        return $query;
    }

    public function validateThirdPartyRequest($app_key, $app_secret)
    {
        $app = ThirdPartyApp::where('app_key', $app_key)
            ->where('app_secret', $app_secret)
            ->first();

        if (! $app) {
            return false;
        }

        $apiKeyExists = ApiKey::where('msg91_third_party_id', $app->id)->exists();

        return $apiKeyExists;
    }

    public function getThirdPartyMsgDetails($thirdPartyId)
    {
        $app = ThirdPartyApp::find($thirdPartyId);

        if (! $app) {
            return errorResponse(__('message.third_party_not_found'));
        }

        return successResponse('', [
            'app_key' => $app->app_key,
            'app_secret' => $app->app_secret,
        ]);
    }

    public function getMsgStauts()
    {
        $status = Msg91Status::orderBy('status_label')->pluck('status_label');

        return successResponse('', $status);
    }
}
