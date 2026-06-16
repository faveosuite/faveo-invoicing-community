<?php

namespace App\License\Controllers\AflCallbacks;

use App\License\Controllers\Traits\AflCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ConnectionTestController extends Controller
{
    use AflCallbackHelpers;

    public function __construct(protected LicenseValidator $validator)
    {
    }

    /**
     * Test connection between Faveo and License Manager
     * POST /apl_callbacks/connection_test.php  OR  POST /api/ConnectionTest.
     */
    public function connection(Request $request)
    {
        $product_id = $request->input('product_id');
        $connection_hash = $request->input('connection_hash');

        if (! $this->validator->isValidConnection($product_id, $connection_hash)) {
            return response('<connection_test>Failed</connection_test>', 400)
                ->header('Content-Type', 'text/plain');
        }

        return response('<connection_test>OK</connection_test>')
            ->header('Content-Type', 'text/plain');
    }
}
