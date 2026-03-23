<?php

namespace App\Http\Controllers\Common\Monitoring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * API endpoint: returns JSON indicating whether Pulse/Horizon is accessible.
     */
    public function checkPulseHorizon(Request $request)
    {
        $type = strtolower((string) $request->get('type', ''));

        if (! in_array($type, ['pulse', 'horizon', 'clockwork'], true)) {
            return errorResponse('Invalid monitoring type', 400);
        }

        $basePath = trim(parse_url(url('/'), PHP_URL_PATH) ?? '', '/');
        $installedInSubdirectory = ! empty($basePath);

        $titleKeys = [
            'pulse' => 'message.pulse_could_not_load',
            'horizon' => 'message.horizon_could_not_load',
            'clockwork' => 'message.clockwork_could_not_load',
        ];
        $toolLabels = [
            'pulse' => 'Pulse',
            'horizon' => 'Horizon',
            'clockwork' => 'Clockwork',
        ];

        $title = __($titleKeys[$type]);
        $toolLabel = $toolLabels[$type];

        return successResponse('', [
            'type' => $type,
            'allowed' => ! $installedInSubdirectory,
            'reason' => $installedInSubdirectory ? 'invalid_installation_path' : null,
            'message' => $installedInSubdirectory ? ($title.'. '.__('message.monitoring_redirect_reason', ['tool' => $toolLabel])) : null,
        ]);
    }
}
