<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;

class ApiController extends Controller
{
    public function logCSP(Request $request): \Illuminate\Http\JsonResponse
    {
        $content = $request->getContent();

        $json = json_decode($content, associative: true);

        Log::channel('csp')->info('CSP Report Received', $json ?? ['raw' => $content]);

        return successResponse('CSP report received successfully', '200');
    }
}
