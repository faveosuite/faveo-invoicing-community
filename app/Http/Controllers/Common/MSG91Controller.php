<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Common\MsgDeliveryReports;
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
                            'failure_reason' => $singleReport['failureReason'] ?? null,
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

    public function updateOtpRequest($requestId, $userID = null)
    {
        MsgDeliveryReports::updateOrCreate(
            ['request_id' => $requestId],
            ['user_id' => $userID]
        );
    }

    public function msg91Reports()
    {
        return view('themes.default1.common.sms.msgReports');
    }

    public function getMsg91Reports(Request $request)
    {
        $response = MsgDeliveryReports::all();

        return successResponse('success', $response);
    }
}
