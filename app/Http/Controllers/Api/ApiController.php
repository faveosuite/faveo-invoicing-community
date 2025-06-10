<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Order\Order;
use App\Model\Product\Product;
use Exception;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function checkDomain(Request $request)
    {
        try {
            // $result = 'fails';
            $result = 'success';
            // $url = $request->input('url');
            // $url2 = preg_replace('#^https?://#', '', $url);

            // //            if (ends_with($domain, '/')) {
            // //                $domain = substr_replace($domain,"", -1, 1);
            // //            }

            // $url_info = parse_url($url);
            // $domain1 = $url_info['host'];
            // $url1 = preg_replace('#^www\.(.+\.)#i', '$1', $url_info['host']); //remove www from domain
            // $domain2 = 'www.'.$url1;
            // $domain1check = $url1.','.$domain2;
            // $orders = new Order();
            // $order = $orders->where('domain', $domain1check)->orWhere('domain', $url1)->orWhere('domain', $domain2)->orWhere('domain', $url2)->first();
            // if ($order) {
            //     $product = Product::where('id', $order->product)->pluck('name')->first();
            //     $result = 'success';
            // }

            return response()->json(compact('result'));
        } catch (Exception $ex) {
            $error = $ex->getMessage();

            return response()->json(compact('error'));
        }
    }

    public function logCSP(Request $request)
    {
        $content = $request->getContent();

        $json = json_decode($content, true);

        \Log::channel('csp')->info('CSP Report Received', $json ?? ['raw' => $content]);

        return successResponse('CSP report received successfully', 200);
    }
}
