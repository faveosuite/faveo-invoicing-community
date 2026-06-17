<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Log;

class ApiController extends Controller
{

    public function logCSP(Request $request)
    {
        $content = $request->getContent();

        $json = json_decode($content, true);

        Log::channel('csp')->info('CSP Report Received', $json ?? ['raw' => $content]);

        return successResponse('CSP report received successfully', 200);
    }
}
