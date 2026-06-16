<?php

namespace App\License\Controllers\AfuCallbacks;

use App\Facades\Attach;
use App\License\Controllers\Traits\AfuCallbackHelpers;
use App\License\Helpers\LicenseValidator;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadFileController extends Controller
{
    use AfuCallbackHelpers;

    public function __construct(protected LicenseValidator $validator)
    {
    }

    /**
     * Download version file
     * POST /aus_callbacks/download_file.php  OR  POST /api/downloadFile.
     */
    public function downloadFile(Request $request)
    {
        $product_id = $request->input('product_id');
        $product_key = $request->input('product_key');
        $version_number = $request->input('version_number');
        $user_local_path = $request->input('user_local_path');
        $script_signature = $request->input('script_signature');
        $ip = $this->validator->resolveIp($request);

        // Validate basic request
        if (! $this->validator->isValidAfuRequest($ip, $product_id, $product_key, $user_local_path, $script_signature)) {
            return $this->notificationResponse('notification_unknown_error', []);
        }

        // Check banned
        if ($this->validator->isBanned($ip)) {
            return $this->notificationResponse('notification_host_banned', []);
        }

        // Find product (original uses AND for both product_id and product_key)
        $product = Product::where('id', $product_id)
            ->where('product_key', $product_key)
            ->first();

        if (! $product) {
            return $this->notificationResponse('notification_product_not_found', []);
        }

        // Get specified version or latest active one
        if (! empty($version_number)) {
            $version = ProductUpload::where('product_id', $product->id)
                ->where('version', $version_number)
                ->first();
        } else {
            $version = ProductUpload::where('product_id', $product->id)
                ->active()
                ->orderBy('id', 'desc')
                ->first();
        }

        if (! $version) {
            $notifKey = ! empty($version_number)
                ? 'notification_version_not_found'
                : 'notification_product_no_versions';

            return $this->notificationResponse($notifKey, []);
        }

        // Verify script signature
        if (! $this->validator->verifyAfuScriptSignature($script_signature, $product_id, $product_key)) {
            return $this->notificationResponse('notification_invalid_signature', []);
        }

        // Check version status
        if (! $version->status) {
            return $this->notificationResponse('notification_version_inactive', []);
        }

        // Check version expiration
        if ($this->validator->verifyDateTime($version->version_expire_date, 'Y-m-d')
            && $version->version_expire_date < date('Y-m-d')) {
            return $this->notificationResponse('notification_version_expired', []);
        }

        $filePath = 'products/'.$version->file;

        if (empty($version->file) || ! Attach::exists($filePath)) {
            return $this->notificationResponse('notification_install_archive_not_found', []);
        }

        $version->increment('version_install_count');

        $this->logCallback($product->id, $version->id, 2, $ip, $user_local_path);

        $filename = basename($filePath);
        $signature = $this->generateSignature($product->id, $product_key);

        $response = new StreamedResponse(function () use ($filePath): void {
            $stream = Attach::readStream($filePath);
            while (! feof($stream)) {
                echo fread($stream, 1024 * 8);
            }

            fclose($stream);
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('notification_case', 'notification_operation_ok');
        $response->headers->set('notification_server_signature', $signature);

        return $response;
    }
}
