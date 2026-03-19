<?php

namespace App\Http\Controllers\License;

use App\Http\Controllers\Controller;
use App\Model\Order\Order;
use App\Model\Product\Product;

class LicenseController extends Controller
{
    private LicenseService $licenseService;

    public function __construct()
    {
        $this->licenseService = new LicenseService();
    }

    private function postCurl($post_url, $post_info, $token = null)
    {
        throttleApiRequest($post_url);

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

    /**
     * Get request to the License mnanager.
     */
    private function getCurl($get_url, $token = null)
    {
        throttleApiRequest($get_url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $get_url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BEARER);
        curl_setopt($ch, CURLOPT_XOAUTH2_BEARER, $token);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 90);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (curl_exec($ch) === false) {
            echo 'Curl error: '.curl_error($ch);
        }
        $content = curl_exec($ch);
        curl_close($ch);

        return json_decode($content, true);
    }

    /**
     * Return API key and API url.
     */
    public function getLicensekey()
    {
        $url = $this->licenseService->getUrl();
        $token = $this->licenseService->getValidToken();
        $getkey = $this->getCurl($url.'api/admin/viewApiKeys', $token);

        return ['data' => $getkey, 'url' => $url];
    }

    /*
    *  Add New Product
    */
    public function addNewProduct($product_name, $product_sku)
    {
        try {
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();

            $addProduct = $this->postCurl($url.'api/admin/products/add', "api_key_secret=$api_key_secret&product_title=$product_name&product_sku=$product_sku&product_status=1", $token);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    /*
   *  Add New User
   */
    public function addNewUser($first_name, $last_name, $email)
    {
        try {
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();
            $addProduct = $this->postCurl($url.'api/admin/clients/add', "api_key_secret=$api_key_secret&client_fname=$first_name&client_lname=$last_name&client_email=$email&client_role='client'&client_status=1", $token);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    /*
   *  Edit Product
   */
    public function editProduct($product_name, $product_sku)
    {
        try {
            $productId = $this->searchProductId($product_sku);
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();
            $addProduct = $this->postCurl($url.'api/admin/products/edit', "api_key_secret=$api_key_secret&product_id=$productId&product_title=$product_name&product_sku=$product_sku&product_status=1", $token);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    /*
   *  Search for product id while updating client
   */
    public function searchProductId($product_sku)
    {
        try {
            $productId = '';
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();
            $getProductId = $this->postCurl($url.'api/admin/search', "api_key_secret=$api_key_secret&search_type=product&search_keyword=$product_sku&isLicenseSearchApi=1", $token);
            $details = json_decode($getProductId);

            if ($details->api_error_detected == 0 && is_array($details->page_message)) {//This is not true if Product_sku is updated
                $productId = $details->page_message[0]->product_id;
            }

            return $productId;
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    public function deleteProductFromAPL($product)
    {
        try {
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $productId = $this->searchProductId($product->product_sku);
            $token = $this->licenseService->getValidToken();
            $productTitle = $product->name;
            $productSku = $product->sku;
            $delProduct = $this->postCurl($url.'api/admin/products/delete', "api_key_secret=$api_key_secret&product_id=$productId&product_title=$productTitle&product_sku=$productSku&product_status=1&delete_record=1", $token);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    /*
   *  Edit User
   */
    public function editUserInLicensing($first_name, $last_name, $email)
    {
        try {
            $userId = $this->searchForUserId($email);
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();
            $addProduct = $this->postCurl($url.'api/admin/clients/edit', "api_key_secret=$api_key_secret&client_id=$userId&client_fname=$first_name&client_lname=$last_name&client_email=$email&client_role='client'&client_status=1", $token);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    /*
   *  Search for user id while updating client
   */
    public function searchForUserId($email)
    {
        try {
            $userId = '';
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();
            $getUserId = $this->postCurl($url.'api/admin/search', "api_key_secret=$api_key_secret&search_type=client&search_keyword=$email&isLicenseSearchApi=1", $token);

            $details = json_decode($getUserId);
            if ($details->api_error_detected == 0 && is_array($details->page_message)) {//This is not true if email is updated
                $userId = $details->page_message[0]->client_id;
            }

            return $userId;
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    /*
    *  Create New License For User
    */
    public function createNewLicene($orderid, $product, $user_id, $licenseExpiry, $updatesExpiry, $supportExpiry, $serial_key)
    {
        try {
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();

            $sku = Product::where('id', $product)->first()->product_sku;

            $licenseExpiry = ($licenseExpiry != '') ? $licenseExpiry->toDateString() : '';
            $updatesExpiry = ($updatesExpiry != '') ? $updatesExpiry->toDateString() : '';
            $supportExpiry = ($supportExpiry != '') ? $supportExpiry->toDateString() : '';
            $order = Order::where('id', $orderid)->first();

            $orderNo = $order->number;
            $domain = $order->domain;
            $ipAndDomain = $this->getIpAndDomain($domain);
            $ip = $ipAndDomain['ip'];
            $domain = $ipAndDomain['domain'];
            $requireDomain = $ipAndDomain['requireDomain'];
            $productId = $this->searchProductId($sku);
            $token = $this->licenseService->getValidToken();

            $addLicense = $this->postCurl($url.'api/admin/license/add', "api_key_secret=$api_key_secret&product_id=$productId&license_code=$serial_key&license_require_domain=1&license_status=1&license_order_number=$orderNo&license_domain=$domain&license_ip=$ip&license_require_domain=$requireDomain&license_limit=6&license_expire_date=$licenseExpiry&license_updates_date=$updatesExpiry&license_support_date=$supportExpiry&license_disable_ip_verification=0&license_limit=1", $token);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }

        //return response(['message'=>'its created','data'=> $addLicense]);
    }

    /*
    *  Edit Existing License
    */
    public function updateLicensedDomain($licenseCode, $domain, $productId, $licenseExpiry, $updatesExpiry, $supportExpiry, $orderNo, $license_limit = 2, $requiredomain = 1)
    {
        try {
            $l_expiry = '';
            $s_expiry = '';
            $u_expiry = '';
            if (strtotime($licenseExpiry) > 1) {
                $l_expiry = date('Y-m-d', strtotime($licenseExpiry));
            }
            if (strtotime($updatesExpiry) > 1) {
                $u_expiry = date('Y-m-d', strtotime($updatesExpiry));
            }
            if (strtotime($supportExpiry) > 1) {
                $s_expiry = date('Y-m-d', strtotime($supportExpiry));
            }
            $url = $this->licenseService->getUrl();
            $ipAndDomain = $this->getIpAndDomain($domain);
            $ip = $ipAndDomain['ip'];
            $domain = $ipAndDomain['domain'];
            $requireDomain = $ipAndDomain['requireDomain'];
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $searchLicense = $this->searchLicenseId($licenseCode, $productId);
            $token = $this->licenseService->getValidToken();
            $licenseId = $searchLicense['licenseId'];
            $productId = $searchLicense['productId'];
            $licenseCode = $searchLicense['code'];
            $updateLicense = $this->postCurl($url.'api/admin/license/edit', "api_key_secret=$api_key_secret&product_id=$productId&license_code=$licenseCode&license_id=$licenseId&license_order_number=$orderNo&license_require_domain=$requireDomain&license_status=1&license_expire_date=$l_expiry&license_updates_date=$u_expiry&license_support_date=$s_expiry&license_domain=$domain&license_ip=$ip&license_limit=$license_limit", $token);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    /**
     * Get the Ip and domain that is to be entered in License Manager.
     *
     * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
     *
     * @date   2019-05-11T11:31:07+0530
     *
     * @param  string  $domain
     * @return array
     */
    protected function getIpAndDomain($domain)
    {
        if ($domain != '') {
            if (ip2long($domain)) {
                return ['ip' => $domain, 'domain' => '', 'requireDomain' => 0];
            } else {
                return ['ip' => '', 'domain' => $domain, 'requireDomain' => 1];
            }
        } else {
            return ['ip' => '', 'domain' => '', 'requireDomain' => 0];
        }
    }

    public function searchLicenseId($licenseCode, $productId)
    {
        try {
            $license = '';
            $product = '';
            $code = '';
            $limit = '';
            $ipOrDomain = '';
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();
            $getLicenseId = $this->postCurl($url.'api/admin/search', "api_key_secret=$api_key_secret&search_type=license&search_keyword=$licenseCode&isLicenseSearchApi=1", $token);
            $details = json_decode($getLicenseId);
            if ($details->api_error_detected == 0 && is_array($details->page_message)) {
                foreach ($details->page_message as $detail) {
                    if ($detail->product_id == $productId) {
                        $license = $detail->license_id;
                        $product = $detail->product_id;
                        $code = $detail->license_code;
                        $limit = $detail->license_limit;
                        $ipOrDomain = $detail->license_require_domain;
                    }
                }
            }

            return ['productId' => $product, 'code' => $code, 'licenseId' => $license, 'allowedInstalltion' => $ipOrDomain, 'installationLimit' => $limit];
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    //Update the Installation status as Inactive after Licensed Domain Is Chnaged
    public function updateInstalledDomain($licenseCode, $productId)
    {
        try {
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();

            //Search for the Installation Id
            $searchInstallationId = $this->searchInstallationId($licenseCode);
            $details = json_decode($searchInstallationId);

            if ($details->api_error_detected == 0 && is_array($details->page_message)) {
                $token = $this->licenseService->getValidToken();
                foreach ($details->page_message as $detail) {
                    if ($detail->product_id == $productId) {
                        $installation_id = $detail->installation_id;
                        $installation_ip = $detail->installation_ip;
                        //delete all existing installation
                        $updateInstallation = $this->postCurl($url.'api/admin/installations/edit', "api_key_secret=$api_key_secret&installation_id=$installation_id&installation_ip=$installation_ip&installation_status=0&delete_record=1", $token);
                    }
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    public function searchInstallationId($licenseCode)
    {
        try {
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();
            $getInstallId = $this->postCurl($url.'api/admin/search', "api_key_secret=$api_key_secret&search_type=installation&search_keyword=$licenseCode&isLicenseSearchApi=1", $token);

            return $getInstallId;
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    public function searchInstallationPath($licenseCode, $productId)
    {
        $installation_domain = [];
        $installation_ip = [];
        $installation_date = [];
        $installation_status = [];
        $details = json_decode($this->searchInstallationId($licenseCode));
        if ($details && $details->api_error_detected == 0 && is_array($details->page_message)) {
            foreach ($details->page_message as $detail) {
                if ($detail->product_id == $productId) {
                    // $installation_domain[] = "<a href=https://$detail->installation_domain target = '_blank'>  "."$detail->installation_domain</a>".' | '.$detail->installation_ip;
                    $installation_domain[] = $detail->installation_domain;
                    $installation_ip[] = $detail->installation_ip;
                    $installation_date[] = $detail->installation_date;
                    $installation_status[] = $detail->installation_status;
                }
            }
        }

        return ['installed_path' => $installation_domain, 'installed_ip' => $installation_ip, 'installation_date' => $installation_date, 'installation_status' => $installation_status];
    }

    //Update  Expiration Date After Renewal
    public function updateExpirationDate($licenseCode, $expiryDate, $productId, $domain, $orderNo, $licenseExpiry, $supportExpiry, $license_limit = 2, $requiredomain = 1)
    {
        try {
            $url = $this->licenseService->getUrl();
            $ipAndDomain = $this->getIpAndDomain($domain);
            $ip = $ipAndDomain['ip'];
            $domain = $ipAndDomain['domain'];
            $requireDomain = $ipAndDomain['requireDomain'];
            $api_key_secret = $this->licenseService->getApiKeySecret();

            $searchLicense = $this->searchLicenseId($licenseCode, $productId);
            $token = $this->licenseService->getValidToken();
            $licenseId = $searchLicense['licenseId'];
            $productId = $searchLicense['productId'];
            $code = $searchLicense['code'];
            $updateLicense = $this->postCurl($url.'api/admin/license/edit', "api_key_secret=$api_key_secret&product_id=$productId&license_code=$code&license_id=$licenseId&license_order_number=$orderNo&license_domain=$domain&license_ip=$ip&license_require_domain=$requireDomain&license_status=1&license_expire_date=$licenseExpiry&license_updates_date=$expiryDate&license_support_date=$supportExpiry&license_limit=$license_limit", $token);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    public function getNoOfAllowedInstallation($licenseCode, $productId)
    {
        $searchLicense = $this->searchLicenseId($licenseCode, $productId);

        return $searchLicense['installationLimit'];
    }

    public function getInstallPreference($licenseCode, $productId)
    {
        $searchLicense = $this->searchLicenseId($licenseCode, $productId);

        return $searchLicense['allowedInstalltion'];
    }

    public function deActivateTheLicense($licenseCode)
    {
        try {
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();

            $this->postCurl($url.'api/admin/license/deactivate', "api_key_secret=$api_key_secret&license_code=$licenseCode", $token);
        } catch (\Exception $ex) {
            \Logger::exception($ex);

            return;
        }
    }

    public function reissueDomain($installationPath)
    {
        $url = $this->licenseService->getUrl();
        $api_key_secret = $this->licenseService->getApiKeySecret();
        $token = $this->licenseService->getValidToken();

        $this->postCurl($url.'api/admin/installation/reissue', "api_key_secret=$api_key_secret&installation_path=$installationPath", $token);
    }

    public function updateLicense($license_code, $oldLicense)
    {
        $url = $this->licenseService->getUrl();
        $api_key_secret = $this->licenseService->getApiKeySecret();
        $token = $this->licenseService->getValidToken();
        $this->postCurl($url.'api/admin/license/updateLicenseCode', "api_key_secret=$api_key_secret&license_code=$license_code&old_license_code=$oldLicense", $token);
    }

    public function licenseRedirect($orderNumber)
    {
        return redirect('/orders/'.Order::where('number', $orderNumber)->value('id'));
    }

    public function syncTheAddonForALicense($product_ids, $license_code, $options = [])
    {
        $url = $this->licenseService->getUrl();
        $api_key_secret = $this->licenseService->getApiKeySecret();
        $token = $this->licenseService->getValidToken();
        // Convert arrays to JSON for proper request formatting
        $options = json_encode($options);

        $postData = http_build_query([
            'api_key_secret' => $api_key_secret,
            'license_code' => $license_code,
            'product_ids' => $product_ids,
            'options' => $options,
        ]);

        $this->postCurl($url.'api/admin/license/syncAddonLicense', $postData, $token);
    }

    public function getInstallationLogsDetails($license_code)
    {
        $url = $this->licenseService->getUrl();
        $api_key_secret = $this->licenseService->getApiKeySecret();
        $token = $this->licenseService->getValidToken();
        $details = json_decode($this->postCurl($url.'api/admin/getInstallationLogs', "api_key_secret=$api_key_secret&license_code=$license_code", $token));

        if ($details && $details->api_error_detected == 0 && is_array($details->page_message)) {
            $installation_details = collect($details->page_message)->map(function ($item) {
                return [
                    'installation_domain' => $item->installation_domain,
                    'installation_ip' => $item->installation_ip,
                    'installation_last_active_date' => $item->installation_last_active_date,
                    'installation_status' => $item->installation_status,
                    'version_number' => $item->version_number,
                ];
            })->toArray(); // Convert the collection back to an array if needed
        } else {
            $installation_details = []; // Handle the case when there are no details or an error occurs
        }

        return $installation_details;
    }

    public function updateInstallationLogs($root_url, $version_number, $installation_ip, $licenseCode)
    {
        try {
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();
            $details = json_decode($this->postCurl($url.'api/admin/updateInstallationLogs', "api_key_secret=$api_key_secret&root_url=$root_url&version_number=$version_number&installation_ip=$installation_ip&license_code=$licenseCode", $token));
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    public function searchProductUsingLicense($licenseCode)
    {
        try {
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();
            $getLicenseId = $this->postCurl($url.'api/admin/search', "api_key_secret=$api_key_secret&search_type=license&search_keyword=$licenseCode&isLicenseSearchApi=1", $token);
            $details = json_decode($getLicenseId, true);
            if (
                $details &&
                ($details['api_error_detected'] ?? 1) == 0 &&
                isset($details['page_message'][0])
            ) {
                return collect($details['page_message'])->toArray();
            }

            return [];
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }

    public function searchProductUsingProductKey($productKey)
    {
        try {
            $url = $this->licenseService->getUrl();
            $api_key_secret = $this->licenseService->getApiKeySecret();
            $token = $this->licenseService->getValidToken();

            return $this->getCurl($url.'api/admin/getProductIdbyKey?'."api_key_secret=$api_key_secret&product_key=$productKey", $token);
        } catch (\Exception $ex) {
            throw new \Exception(__('message.configure_valid_license'));
        }
    }
}