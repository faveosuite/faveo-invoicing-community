<?php

namespace App\License\Controllers\AflCallbacks;

use App\License\Controllers\Traits\AflCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ConnectionTestController extends Controller
{
    use AflCallbackHelpers;

    protected LicenseValidator $validator;

    public function __construct(LicenseValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Test connection between Faveo and License Manager
     * POST /apl_callbacks/connection_test.php  OR  POST /api/ConnectionTest.
     */
    public function connection(Request $request)
    {
        $product_id = $request->input('product_id');
        $connection_hash = $request->input('connection_hash');
        $root_url = $request->input('root_url');

        if (! $this->validator->isValidConnection($product_id, $connection_hash)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        return $this->notificationResponse('notification_license_ok', [
            'connection' => 'successful',
        ], $product_id, '', '', $root_url);
    }
}
