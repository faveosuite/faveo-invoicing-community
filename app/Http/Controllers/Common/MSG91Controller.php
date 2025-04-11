<?php

namespace App\Http\Controllers\Common;

use App\ApiKey;
use App\Http\Controllers\Controller;
use App\Model\Common\MsgDeliveryReports;
use App\ThirdPartyApp;
use Carbon\Carbon;
use DataTables;
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
    public function handleReports(Request $request): void
    {
        if (! $this->validateThirdPartyRequest($request)) {
            return;
        }
        try {
            $jsonData = $request->input('data');

            // Ensure data is an array
            $reports = is_string($jsonData) ? json_decode($jsonData, true) : $jsonData;

            // Process each report
            \DB::transaction(function () use ($reports) {
                foreach ($reports as $reportGroup) {
                    $requestId = $reportGroup['requestId'];

                    // Process individual number reports
                    foreach ($reportGroup['report'] as $singleReport) {
                        $this->processIndividualReport([
                            'request_id' => $requestId,
                            'number' => $singleReport['number'],
                            'status' => $singleReport['status'],
                            'date' => $singleReport['date'] ?? now()->toDateTimeString(),
                            'failure_reason' => $singleReport['failedReason'] ?? null,
                        ]);
                    }
                }
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
        $record = MsgDeliveryReports::where('request_id', $reportData['request_id'])->first();

        if ($record) {
            $record->update([
                'status' => $reportData['status'],
                'date' => $reportData['date'],
                'failure_reason' => $reportData['failure_reason'],
            ]);
        }
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

    public function msg91Reports()
    {
        return view('themes.default1.common.sms.msgReports');
    }

    public function getMsg91Reports(Request $request)
    {
        $query = $this->msg91ReportQuery($request);

        return DataTables::of($query)
            ->addColumn('request_id', function ($model) {
                return $model->request_id;
            })
            ->addColumn('user.full_name', function ($model) {
                return $model->user ? $model->user->full_name : '---';
            })
            ->addColumn('user.email', function ($model) {
                return $model->user ? $model->user->email : '---';
            })
            ->addColumn('readable_status', function ($model) {
                return $model->readable_status;
            })
            ->addColumn('date', function ($model) {
                return $model->date ? getDateHtml($model->date) : '---';
            })
            ->editColumn('failure_reason', function ($model) {
                return $model->failure_reason ?? '---';
            })
            ->editColumn('mobile_number', function ($model) {
                return $model->mobile_number ?? '---';
            })

            // Filtering
            ->filterColumn('user.full_name', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->whereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$keyword}%"]);
                });
            })
            ->filterColumn('user.email', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('email', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('request_id', function ($query, $keyword) {
                $query->where('request_id', 'like', "%{$keyword}%");
            })
            ->filterColumn('readable_status', function ($query, $keyword) {
                $query->where('status', 'like', "%{$keyword}%");
            })
            ->filterColumn('date', function ($query, $keyword) {
                $query->where('date', 'like', "%{$keyword}%");
            })
            ->filterColumn('failure_reason', function ($query, $keyword) {
                $query->where('failure_reason', 'like', "%{$keyword}%");
            })
            ->filterColumn('mobile_number', function ($query, $keyword) {
                $query->where('mobile_number', 'like', "%{$keyword}%");
            })

            // Sorting
            ->orderColumn('request_id', 'request_id $1')
            ->orderColumn('mobile_number', 'mobile_number $1')
            ->orderColumn('readable_status', 'status $1')
            ->orderColumn('date', 'date $1')
            ->orderColumn('failure_reason', 'failure_reason $1')
            ->orderColumn('user.full_name', function ($query, $direction) {
                $query->leftJoin('users as u2', 'msg_delivery_reports.user_id', '=', 'u2.id')
                    ->orderByRaw("CONCAT(u2.first_name, ' ', u2.last_name) {$direction}")
                    ->select('msg_delivery_reports.*');
            })
            ->orderColumn('user.email', function ($query, $direction) {
                $query->leftJoin('users as u2', 'msg_delivery_reports.user_id', '=', 'u2.id')
                    ->orderBy('u2.email', $direction)
                    ->select('msg_delivery_reports.*');
            })

            ->rawColumns(['date'])
            ->make(true);
    }

    public function msg91ReportQuery(Request $request)
    {
        $query = MsgDeliveryReports::with(['user']);

        // Individual field filters
        $query->when($request->filled('request_id'), fn ($q) => $q->where('request_id', 'like', '%'.$request->input('request_id').'%'));

        $query->when($request->filled('mobile_number'), function ($q) use ($request) {
            $q->when($request->filled('country_iso'), function ($q) use ($request) {
                $q->where('country_iso', $request->input('country_iso'));
            })->where('mobile_number', 'like', '%'.$request->input('mobile_number').'%');
        });

        $query->when($request->filled('full_name'), function ($q) use ($request) {
            $q->whereHas('user', function ($subQuery) use ($request) {
                $subQuery->whereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ['%'.$request->input('full_name').'%']);
            });
        });

        $query->when($request->filled('failure_reason'), fn ($q) => $q->where('failure_reason', 'like', '%'.$request->input('failure_reason').'%'));

        $query->when($request->filled('status'), function ($q) use ($request) {
            $status = $request->input('status');

            if ($status === 'rejected') {
                $q->whereIn('status', [16, 25]);
            } else {
                $q->where('status', $status);
            }
        });

        $query->when($request->filled('date_from'), function ($q) use ($request) {
            $from = Carbon::createFromFormat('m/d/Y', $request->input('date_from'))->format('Y-m-d');
            $q->whereDate('date', '>=', $from);
        });

        $query->when($request->filled('date_to'), function ($q) use ($request) {
            $to = Carbon::createFromFormat('m/d/Y', $request->input('date_to'))->format('Y-m-d');
            $q->whereDate('date', '<=', $to);
        });

        $query->when($request->filled('email'), function ($q) use ($request) {
            $q->whereHas('user', fn ($subQuery) => $subQuery->where('email', 'like', '%'.$request->input('email').'%')
            );
        });

        return $query;
    }

    public function validateThirdPartyRequest($request)
    {
        $app_key = $request->input('app_key');
        $app_secret = $request->input('app_secret');

        $app = ThirdPartyApp::where('app_key', $app_key)
            ->where('app_secret', $app_secret)
            ->first();

        if (! $app) {
            return false;
        }

        $apiKeyExists = ApiKey::where('msg91_third_party_id', $app->id)->exists();

        return $apiKeyExists;
    }
}
