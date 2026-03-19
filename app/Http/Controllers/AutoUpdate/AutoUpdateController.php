<?php

namespace App\Http\Controllers\AutoUpdate;

use App\Http\Controllers\Controller;
use App\Http\Controllers\License\LicenseService;

class AutoUpdateController extends Controller
{
    private LicenseService $licenseService;

    //need to remove this once we deprecate updates.faveohelpdesk.com
    private $updateUrl = '';

    private $update_api_secret = '';

    public function __construct()
    {
        $this->licenseService = new LicenseService();
    }

    private function postCurl($post_url, $post_info, $token = null)
    {
        if (! empty($token)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $post_url);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BEARER);
            curl_setopt($ch, CURLOPT_XOAUTH2_BEARER, $token);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_info);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $result = curl_exec($ch);
            curl_close($ch);

            return $result;
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $post_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_info);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $result = curl_exec($ch);
            curl_close($ch);

            return $result;
        }
    }

    /*
    *  Add New Product
    */
    public function addNewProductToAUS($product_id, $product_name, $product_sku)
    {
        try {
            $url = $this->licenseService->getUrl();
            $key = str_random(16);
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();

            $addProduct = $this->postCurl($url.'api/admin/products/UpdateAdd', "api_key_secret=$api_key_secret&product_id=$product_id&product_title=$product_name&product_sku=$product_sku&product_key=$key&product_status=1", $token);
            //need to remove this once we deprecate updates.faveohelpdesk.com
            $anotheradd = $this->postCurl($this->updateUrl, "api_key_secret=$this->update_api_secret&api_function=products_add&product_title=$product_name&product_sku=$product_sku&product_key=$key&product_status=1");
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    /*
    *  Add New Version
    */

    public function addNewVersion($product_id, $version_number, $upgrade_zip_file, $version_status)
    {
        try {
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();
            $addNewVersion = $this->postCurl($url.'api/admin/versions/add', "api_key_secret=$api_key_secret&product_id=$product_id&version_number=$version_number&version_upgrade_file=$upgrade_zip_file&version_status=$version_status&product_status=1", $token);
            //need to remove this once we deprecate updates.faveohelpdesk.com
            $anotherVersion = $this->postCurl($this->updateUrl, "api_key_secret=$this->update_api_secret&api_function=versions_add&product_id=$product_id&version_number=$version_number&version_upgrade_file=$upgrade_zip_file&version_status=$version_status&product_status=1");
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
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $searchLicense = $this->searchVersion($version_number, $product_sku);
            $versionId = $searchLicense['version_id'];
            $productId = $searchLicense['product_id'];
            $token = $this->licenseService->getValidToken();
            $addNewVersion = $this->postCurl($url.'api/admin/versions/edit', "api_key_secret=$api_key_secret&product_id=productId&version_id=$versionId&version_number=$version_number&version_status=1", $token);
            //need to remove this once we deprecate updates.faveohelpdesk.com
            $editNewVersion = $this->postCurl($this->updateUrl, "api_key_secret=$api_key_secret&api_function=versions_edit&product_id=productId&version_id=$versionId&version_number=$version_number&version_status=1");
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    /*
    *  Search Version
    */
    public function searchVersion($version_number, $product_sku)
    {
        try {
            $versionId = '';
            $productId = '';
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();
            $getVersion = $this->postCurl($url.'api/admin/search', "api_key_secret=$api_key_secret&search_type=version&search_keyword=$product_sku&isLicenseSearchApi=0", $token);
            $details = json_decode($getVersion);
            if ($details->api_error_detected == 0 && is_array($details->page_message)) {
                foreach ($details->page_message as $detail) {
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
