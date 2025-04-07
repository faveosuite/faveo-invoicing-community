<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Common\MsgDeliveryReports;
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
        try {
            $jsonData = $request->input('data');

            // Ensure data is an array
            $reports = is_string($jsonData) ? json_decode($jsonData, true) : $jsonData;

            // Process each report
            \DB::transaction(function () use ($reports) {
                foreach ($reports as $reportGroup) {
                    $requestId = $reportGroup['requestId'];
                    $senderId = $reportGroup['senderId'];

                    // Process individual number reports
                    foreach ($reportGroup['report'] as $singleReport) {
                        $this->processIndividualReport([
                            'request_id' => $requestId,
                            'sender_id' => $senderId,
                            'number' => $singleReport['number'],
                            'status' => $singleReport['status'],
                            'date' => $singleReport['date'] ?? now()->toDateTimeString(),
                            'failure_reason' => $singleReport['failedReason'] ?? null,
                        ]);
                    }
                }
            });
        } catch (\Exception $e) {
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
        MsgDeliveryReports::updateOrCreate(
            ['request_id' => $reportData['request_id']],
            [
                'sender_id' => $reportData['sender_id'],
                'mobile_number' => $reportData['number'],
                'status' => $reportData['status'],
                'date' => $reportData['date'],
                'failure_reason' => $reportData['failure_reason'],
            ]
        );
    }

    public function updateOtpRequest($requestId, $status, $userID = null)
    {
        MsgDeliveryReports::updateOrCreate(
            ['request_id' => $requestId],
            [
                'user_id' => $userID,
                'status' => $status
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
            ->addColumn('formatted_sender_id', function ($model) {
                return $model->formatted_sender_id;
            })
            ->addColumn('readable_status', function ($model) {
                return $model->readable_status;
            })
            ->addColumn('date', function ($model) {
                return $model->date ? $model->date : '---';
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
            ->filterColumn('formatted_sender_id', function ($query, $keyword) {
                $query->where('formatted_sender_id', 'like', "%{$keyword}%");
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
            ->orderColumn('formatted_sender_id', 'formatted_sender_id $1')
            ->orderColumn('readable_status', 'status $1')
            ->orderColumn('date', 'date $1')
            ->orderColumn('failure_reason', 'failure_reason $1')
            ->orderColumn('user.full_name', function ($query, $direction) {
                $query->leftJoin('users', 'msg_delivery_reports.user_id', '=', 'users.id')
                    ->orderByRaw("CONCAT(users.first_name, ' ', users.last_name) {$direction}")
                    ->select('msg_delivery_reports.*');
            })
            ->orderColumn('user.email', function ($query, $direction) {
                $query->leftJoin('users', 'msg_delivery_reports.user_id', '=', 'users.id')
                    ->orderBy('users.email', $direction)
                    ->select('msg_delivery_reports.*');
            })

            ->make(true);
    }

    public function msg91ReportQuery(Request $request)
    {
        $query = MsgDeliveryReports::with('user');

        // Individual field filters
        $query->when($request->filled('request_id'), fn ($q) => $q->where('request_id', 'like', '%'.$request->input('request_id').'%')
        );

        $query->when($request->filled('mobile_number'), fn ($q) => $q->where('mobile_number', 'like', '%'.$request->input('mobile_number').'%')
        );

        $query->when($request->filled('sender_id'), fn ($q) => $q->where('sender_id', 'like', '%'.$request->input('sender_id').'%')
        );

        $query->when($request->filled('failure_reason'), fn ($q) => $q->where('failure_reason', 'like', '%'.$request->input('failure_reason').'%')
        );

        $query->when($request->has('status') && $request->input('status') !== '', fn ($q) => $q->where('status', $request->input('status'))
        );

        $query->when($request->filled('date_from'), fn ($q) => $q->whereDate('date', '>=', $request->input('date_from'))
        );

        $query->when($request->filled('date_to'), fn ($q) => $q->whereDate('date', '<=', $request->input('date_to'))
        );

        $query->when($request->filled('email'), function ($q) use ($request) {
            $q->whereHas('user', fn ($subQuery) => $subQuery->where('email', 'like', '%'.$request->input('email').'%')
            );
        });

        return $query;
    }
}
