<?php

namespace App\Http\Controllers\AutoUpdate;

use App\Http\Controllers\Controller;
use App\Http\Controllers\License\LicenseService;
use App\Streams\License\LicenseStreamHandler;
use Illuminate\Support\Facades\Http;

class AutoUpdateController extends Controller
{
    private LicenseService $licenseService;

    public function __construct()
    {
        $this->licenseService = new LicenseService();
    }

    private function isStreams(): bool
    {
        return true;
    }

    /**
     * Make HTTP POST request to license API.
     */
    private function postRequest(string $endpoint, array $data): array
    {
        $url = $this->licenseService->getUrl().$endpoint;
        throttleApiRequest($url);

        $payload = array_merge(['api_key_secret' => $this->licenseService->getApiKeySecret()], $data);

        $response = Http::asForm()
            ->withOptions(['verify' => false, 'allow_redirects' => true])
            ->withToken($this->licenseService->getValidToken())
            ->post($url, $payload);

        return $response->json() ?? [];
    }

    /*
    *  Add New Product
    */
    public function addNewProductToAUS($product_id, $product_name, $product_sku)
    {
        try {
            $key = str_random(16);

            if ($this->isStreams()) {
                LicenseStreamHandler::addNewProductToAUS($product_id, $product_name, $product_sku, $key);

                return;
            }

            $this->postRequest('api/admin/products/UpdateAdd', [
                'product_id' => $product_id,
                'product_title' => $product_name,
                'product_sku' => $product_sku,
                'product_key' => $key,
                'product_status' => 1,
            ]);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    public function editProductToAUS($product_name, $product_sku)
    {
        try {
            if ($this->isStreams()) {
                LicenseStreamHandler::editProductToAUS($product_name, $product_sku);

                return;
            }

            $productId = $this->searchProductId($product_sku);
            $this->postRequest('api/admin/products/UpdateEdit', [
                'product_id' => $productId,
                'product_title' => $product_name,
                'product_sku' => $product_sku,
                'product_status' => 1,
            ]);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    public function deleteProductFromAUS($product): void
    {
        if ($this->isStreams()) {
            LicenseStreamHandler::deleteProductAUS($product->product_sku);

            return;
        }

        $productId = $this->searchProductId($product->product_sku);
        $this->postRequest('api/admin/products/UpdateDelete', [
            'product_id' => $productId,
        ]);
    }

    /*
    *  Add New Version
    */
    public function addNewVersion($product_id, $version_number, $upgrade_zip_file, $version_status)
    {
        try {
            if ($this->isStreams()) {
                LicenseStreamHandler::addNewVersion($product_id, $version_number, $upgrade_zip_file, $version_status);

                return;
            }

            $this->postRequest('api/admin/versions/add', [
                'product_id' => $product_id,
                'version_number' => $version_number,
                'version_upgrade_file' => $upgrade_zip_file,
                'version_status' => $version_status,
                'product_status' => 1,
            ]);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    /*
    *  Edit Version
    */
    public function editVersion($version_number, $product_sku)
    {
        try {
            if ($this->isStreams()) {
                LicenseStreamHandler::editVersion($version_number, $product_sku);

                return;
            }

            $searchLicense = $this->searchVersion($version_number, $product_sku);

            $this->postRequest('api/admin/versions/edit', [
                'product_id' => $searchLicense['product_id'],
                'version_id' => $searchLicense['version_id'],
                'version_number' => $version_number,
                'version_status' => 1,
            ]);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    private function getStreamSearchResults(string $searchType, string $searchKeyword): array
    {
        return LicenseStreamHandler::search($searchType, $searchKeyword, 0)['result']['data']['data'] ?? [];
    }

    /*
 *  Search for product id while updating client
 */
    public function searchProductId($product_sku): string
    {
        if ($this->isStreams()) {
            $data = $this->getStreamSearchResults('product', $product_sku);

            return ! empty($data) ? ($data[0]['product_id'] ?? '') : '';
        }

        $result = $this->postRequest('api/admin/search', [
            'search_type' => 'product',
            'search_keyword' => $product_sku,
            'isLicenseSearchApi' => 0,
        ]);

        if (($result['api_error_detected'] ?? 1) == 0 && is_array($result['page_message'] ?? [])) {
            return $result['page_message'][0]->product_id ?? '';
        }

        return '';
    }

    /*
    *  Search Version
    */
    public function searchVersion($version_number, $product_sku)
    {
        try {
            if ($this->isStreams()) {
                return LicenseStreamHandler::searchVersion($version_number, $product_sku);
            }

            $versionId = '';
            $productId = '';

            $result = $this->postRequest('api/admin/search', [
                'search_type' => 'version',
                'search_keyword' => $product_sku,
                'isLicenseSearchApi' => 0,
            ]);

            if (($result['api_error_detected'] ?? 1) == 0 && is_array($result['page_message'] ?? [])) {
                foreach ($result['page_message'] as $detail) {
                    $detail = (object) $detail;
                    if ($detail->version_number == $version_number) {
                        $versionId = $detail->version_id;
                        $productId = $detail->product_id;
                    }
                }
            }

            return ['version_id' => $versionId, 'product_id' => $productId];
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }
}
